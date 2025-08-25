<?php
/**
 * Gestion de la base de données
 *
 * @package SunoMusicPlayerDirect
 */

if (!defined('ABSPATH')) {
    exit;
}

class Suno_Database {
    
    /**
     * Crée les tables nécessaires
     */
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'suno_tracks';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            artist varchar(255) DEFAULT '',
            album varchar(255) DEFAULT '',
            genre varchar(100) DEFAULT '',
            description text,
            file_path varchar(500) NOT NULL,
            file_url varchar(500) NOT NULL,
            cover_url varchar(500) DEFAULT '',
            duration int DEFAULT 0,
            plays int DEFAULT 0,
            downloads int DEFAULT 0,
            user_id bigint(20) DEFAULT 0,
            status varchar(20) DEFAULT 'published',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY genre (genre)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Table des statistiques
        $stats_table = $wpdb->prefix . 'suno_stats';
        
        $sql_stats = "CREATE TABLE $stats_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            track_id mediumint(9) NOT NULL,
            user_id bigint(20) DEFAULT 0,
            action varchar(20) NOT NULL,
            ip_address varchar(45) DEFAULT '',
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY track_id (track_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($sql_stats);
    }
    
    /**
     * Récupère toutes les pistes
     */
    public static function get_tracks($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'status' => 'published',
            'user_id' => 0,
            'genre' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array("status = %s");
        $values = array($args['status']);
        
        if ($args['user_id'] > 0) {
            $where[] = "user_id = %d";
            $values[] = $args['user_id'];
        }
        
        if (!empty($args['genre'])) {
            $where[] = "genre = %s";
            $values[] = $args['genre'];
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where);
        
        $values[] = $args['limit'];
        $values[] = $args['offset'];
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            $where_clause 
            ORDER BY {$args['orderby']} {$args['order']} 
            LIMIT %d OFFSET %d",
            $values
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Récupère une piste par ID
     */
    public static function get_track($track_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $track_id
        ));
    }
    
    /**
     * Insère une nouvelle piste
     */
    public static function insert_track($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Met à jour une piste
     */
    public static function update_track($track_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        return $wpdb->update(
            $table_name,
            $data,
            array('id' => $track_id)
        );
    }
    
    /**
     * Supprime une piste
     */
    public static function delete_track($track_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        // Récupérer les infos pour supprimer le fichier
        $track = self::get_track($track_id);
        
        if ($track && file_exists($track->file_path)) {
            unlink($track->file_path);
        }
        
        return $wpdb->delete(
            $table_name,
            array('id' => $track_id)
        );
    }
    
    /**
     * Incrémente le compteur de lectures
     */
    public static function increment_plays($track_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET plays = plays + 1 WHERE id = %d",
            $track_id
        ));
        
        // Enregistrer dans les stats
        self::log_stat($track_id, 'play');
    }
    
    /**
     * Incrémente le compteur de téléchargements
     */
    public static function increment_downloads($track_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET downloads = downloads + 1 WHERE id = %d",
            $track_id
        ));
        
        // Enregistrer dans les stats
        self::log_stat($track_id, 'download');
    }
    
    /**
     * Enregistre une statistique
     */
    private static function log_stat($track_id, $action) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_stats';
        
        $wpdb->insert($table_name, array(
            'track_id' => $track_id,
            'user_id' => get_current_user_id(),
            'action' => $action,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ));
    }
    
    /**
     * Récupère les statistiques d'une piste
     */
    public static function get_track_stats($track_id, $days = 30) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_stats';
        
        $date_limit = date('Y-m-d', strtotime("-$days days"));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, action, COUNT(*) as count 
            FROM $table_name 
            WHERE track_id = %d AND created_at >= %s 
            GROUP BY DATE(created_at), action 
            ORDER BY date DESC",
            $track_id,
            $date_limit
        ));
    }
}
