<?php
/**
 * Gestionnaire des hooks WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoLoader {
    
    protected $actions = array();
    protected $filters = array();
    protected $shortcodes = array();
    
    /**
     * Ajouter une action
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Ajouter un filtre
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Ajouter un shortcode
     */
    public function add_shortcode($tag, $component, $callback) {
        $this->shortcodes[$tag] = array('component' => $component, 'callback' => $callback);
    }
    
    /**
     * Ajouter un hook
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }
    
    /**
     * Exécuter tous les hooks
     */
    public function run() {
        // Actions
        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
        
        // Filtres
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
        
        // Shortcodes
        foreach ($this->shortcodes as $tag => $shortcode) {
            add_shortcode($tag, array($shortcode['component'], $shortcode['callback']));
        }
    }
}