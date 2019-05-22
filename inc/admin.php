<?php

    class admin {

        function reg() {
            add_filter('plugin_action_links_ghmobilemoney/ghmobilemoney.php', [$this, 'manage']);
            add_action( 'plugins_loaded', 'init_your_gateway_class' );
        }

        function manage($links) {
            $link = "<a href='admin.php?page=ghmobilemoney'>Manage</a>";
            array_push($links, $link);
            return $links;

        }

    }
