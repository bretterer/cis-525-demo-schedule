<?php

use Bretterer\DemonstrationScheduling\Migration;

return new class extends Migration {

    public function up(): void {
        $this->upQueries[] = "CREATE TABLE `bookings` (
            `id` int NOT NULL AUTO_INCREMENT,
            `first_name` varchar(255) DEFAULT NULL,
            `last_name` varchar(255) NOT NULL,
            `email` text NOT NULL,
            `umid` varchar(255) NOT NULL,
            `project_title` text NOT NULL,
            `timeslot` int NOT NULL,
            PRIMARY KEY (`id`),
            KEY `timeslot` (`timeslot`),
            CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`timeslot`) REFERENCES `availability` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";


    }

    public function down(): void {
        $this->downQueries[] = "DROP TABLE bookings";

    }

};