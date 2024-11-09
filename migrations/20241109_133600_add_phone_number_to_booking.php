<?php

use Bretterer\DemonstrationScheduling\Migration;

return new class extends Migration {

    public function up(): void {
        $this->upQueries[] = "ALTER TABLE bookings
            ADD COLUMN phone_number VARCHAR(20) AFTER email; ";


    }

    public function down(): void {
        $this->downQueries[] = "ALTER TABLE bookings
            DROP COLUMN phone_number;";

    }

};