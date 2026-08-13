<?php

namespace Database\Seeders;

use App\Models\TsoShipment;
use Illuminate\Database\Seeder;

class TsoShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['unit_type' => 'Hilux Rangga', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO Medan Krakatau', 'no_rangka' => 'MR0ACAEA5S0241401', 'do_date' => '2025-08-10', 'pu_date' => '2025-10-10', 'door_to_port' => '2025-10-10', 'port_to_port' => '2025-10-13'],
            ['unit_type' => 'INNOVA REBORN', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'SUMATRA CAHAYA MANDIRI', 'no_rangka' => 'MHFJB8EMXS1172952', 'do_date' => '2025-10-17', 'pu_date' => '2025-10-17', 'door_to_port' => '2025-10-17', 'port_to_port' => '2025-10-20', 'port_to_door' => '2025-10-24'],
            ['unit_type' => 'AVANZA', 'origin' => 'PDC Sunter Lake', 'destination' => 'Banda Aceh', 'detail_destination' => 'TRAKINDO UTAMA', 'no_rangka' => 'MHKAB1BY8SK113523', 'do_date' => '2025-10-17', 'pu_date' => '2025-10-17', 'door_to_port' => '2025-10-17', 'port_to_port' => '2025-10-20', 'port_to_door' => '2025-10-24'],
            ['unit_type' => 'AVANZA', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TRAKINDO UTAMA', 'no_rangka' => 'MHKAB1BY9SJ006972', 'do_date' => '2025-10-17', 'pu_date' => '2025-10-17', 'door_to_port' => '2025-10-17', 'port_to_port' => '2025-10-20', 'port_to_door' => '2025-10-24'],
            ['unit_type' => 'RUSH 1.5 S A/T GR SPORT', 'origin' => 'PDC Sunter Lake', 'destination' => 'Balikpapan', 'detail_destination' => 'TSO-BALIKPAPAN', 'no_rangka' => 'MHKE8FB3JSK112558', 'do_date' => '2025-10-17', 'pu_date' => '2025-10-17', 'door_to_port' => '2025-10-17', 'port_to_port' => '2025-10-19', 'port_to_door' => '2025-10-21'],
            ['unit_type' => 'HILUX DOBLE CABIN', 'origin' => 'PDC Sunter Lake', 'destination' => 'Banjarmasin', 'detail_destination' => 'TSO-BANJARMASIN', 'no_rangka' => 'MR0KB8CD6S1162925', 'do_date' => '2025-10-21', 'pu_date' => '2025-10-21', 'door_to_port' => '2025-10-21', 'port_to_port' => '2025-10-21', 'port_to_door' => '2025-10-24'],
            ['unit_type' => 'NEW FOTUNER 2,8 VRZ', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO Medan Sisingamangarja', 'no_rangka' => 'MHFBA3WSXS1059963', 'do_date' => '2025-11-01', 'pu_date' => '2025-11-01', 'door_to_port' => '2025-11-01', 'port_to_port' => '2025-11-02', 'port_to_door' => '2025-11-02'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'no_rangka' => 'MHKAA1BY9SJ004075', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Serasi V'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'no_rangka' => 'MHKAA1BY7SJ005046', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Serasi V'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Serasi V'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'no_rangka' => 'MHKAA1BY9SJ005072', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Fajar Bahari 8'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'no_rangka' => 'MHKAA1BY2SJ004984', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Serasi V'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'no_rangka' => 'MHKAA1BY4SJ004694', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Serasi V'],
            ['unit_type' => 'New Avanza 1.3 E M/T', 'origin' => 'PDC Sunter Lake', 'destination' => 'Medan', 'detail_destination' => 'TSO-Medan Amplas', 'no_rangka' => 'MHKAA1BYXSJ004893', 'do_date' => '2025-11-12', 'pu_date' => '2025-11-13', 'door_to_port' => '2025-11-13', 'port_to_port' => '2025-11-17', 'port_to_door' => '2025-11-21', 'vessel_ptp' => 'Ostina'],
        ];

        foreach ($rows as $row) {
            TsoShipment::firstOrCreate([
                'no_rangka' => $row['no_rangka'] ?? null,
                'detail_destination' => $row['detail_destination'] ?? null,
                'do_date' => $row['do_date'] ?? null,
            ], $row);
        }
    }
}
