<?php

function adminer_object() {
    class AdminerSoftware extends Adminer {
        public function credentials() {
            return [
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
            ];
        }

        public function database() {
            config('database.connections.mysql.database');
        }
    }

    return new AdminerSoftware;
}
