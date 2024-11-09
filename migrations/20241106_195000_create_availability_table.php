<?php

use Bretterer\DemonstrationScheduling\Migration;

return new class extends Migration {

    public function up(): void {
        $this->upQueries[] = "CREATE TABLE `availability` (
            `id` int NOT NULL AUTO_INCREMENT,
            `start_time` datetime DEFAULT NULL,
            `end_time` datetime DEFAULT NULL,
            `available_seats` int DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";



    }

    public function down(): void {
        $this->downQueries[] = "DROP TABLE availability";

    }

};