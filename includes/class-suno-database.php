<?php
/**
 * Gestion de la base de données
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoDatabase {
    
    /**
     * Créer les tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table des pistes audio
        $table_tracks = $wpdb->prefix . 'suno_tracks';
        
        $sql_tracks = "CREATE TABLE $table_tracks (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            artist varchar(255) DEFAULT '',
            album varchar(255) DEFAULT '',
            genre varchar(100) DEFAULT '',
            file_url varchar(500) NOT NULL,
            file_path varchar(500) NOT NULL,
            thumbnail_url varchar(500) DEFAULT '',
            duration int DEFAULT 0,
            play_count int DEFAULT 0,
            download_count int DEFAULT 0,
            user_id bigint(20) DEFAULT 0,
            suno_id varchar(100) DEFAULT '',
            description text DEFAULT '',
            tags text DEFAULT '',
            metadata longtext DEFAULT '',
            status varchar(20) DEFAULT 'public',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Table des playlists
        $table_playlists = $wpdb->prefix . 'suno_playlists';
        
        $sql_playlists = "CREATE TABLE $table_playlists (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT '',
            user_id bigint(20) DEFAULT 0,
            tracks text DEFAULT '',
            thumbnail_url varchar(500) DEFAULT '',
            visibility varchar(20) DEFAULT 'public',
            play_count int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY visibility (visibility)
        ) $charset_collate;";
        
        // Table des statistiques
        $table_stats = $wpdb->prefix . 'suno_stats';
        
        $sql_stats = "CREATE TABLE $table_stats (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            track_id mediumint(9) NOT NULL,
            event_type varchar(20) NOT NULL,
            user_id bigint(20) DEFAULT 0,
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            referrer varchar(500) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY track_id (track_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_tracks);
        dbDelta($sql_playlists);
        dbDelta($sql_stats);
        
        // Ajouter la version de la DB
        update_option('suno_player_db_version', '2.0');
    }
    
    /**
     * Obtenir toutes les pistes
     */
    public static function get_tracks($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'user_id' => null,
            'status' => 'public',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table = $wpdb->prefix . 'suno_tracks';
        $where = array();
        
        if ($args['user_id']) {
            $where[] = $wpdb->prepare('user_id = %d', $args['user_id']);
        }
        
        if ($args['status']) {
            $where[] = $wpdb->prepare('status = %s', $args['status']);
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table 
            $where_clause 
            ORDER BY {$args['orderby']} {$args['order']} 
            LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Insérer une piste
     */
    public static function insert_track($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'suno_tracks';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Mettre à jour une piste
     */
    public static function update_track($id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'suno_tracks';
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Supprimer une piste
     */
    public static function delete_track($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'suno_tracks';
        
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Incrémenter le compteur de lecture
     */
    public static function increment_play_count($track_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'suno_tracks';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET play_count = play_count + 1 WHERE id = %d",
            $track_id
        ));
        
        // Enregistrer dans les stats
        self::log_stat($track_id, 'play');
    }
    
    /**
     * Enregistrer une statistique
     */
    public static function log_stat($track_id, $event_type) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'suno_stats';
        
        $data = array(
            'track_id' => $track_id,
            'event_type' => $event_type,
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? ''
        );
        
        $wpdb->insert($table, $data);
    }
}