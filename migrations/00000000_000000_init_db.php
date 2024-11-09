<?php

use Bretterer\DemonstrationScheduling\Migration;

return new class extends Migration {

    public function up(): void {
        $this->upQueries[] = "CREATE TABLE migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        )";



    }

    public function down(): void {
        $this->downQueries[] = "DROP TABLE migrations";

    }

};