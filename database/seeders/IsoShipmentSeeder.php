<?php

namespace Database\Seeders;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use Illuminate\Database\Seeder;

class IsoShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $daratRows = [
            ['source_no' => 1, 'no_so_booking' => '3100551770', 'no_quotation' => '1100221689', 'no_contract' => '2100152405', 'cargo_no_ka' => 'FTR90TE400011R1S', 'no_spb' => 'MHCFTR90TSJ001133', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG TIMUR', 'destination' => 'BEKASI', 'area' => 'JABODETABEK', 'terima_do' => '2025-09-02', 'keluar_dari_pdc' => '2025-09-02', 'at_ptd_dtd' => '2025-09-03'],
            ['source_no' => 2, 'no_so_booking' => '3100551771', 'no_quotation' => '1100221689', 'no_contract' => '2100152405', 'cargo_no_ka' => 'FTR90TE400011R1S', 'no_spb' => 'MHCFTR90TSJ001134', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG TIMUR', 'destination' => 'BEKASI', 'area' => 'JABODETABEK', 'terima_do' => '2025-09-02', 'keluar_dari_pdc' => '2025-09-02', 'at_ptd_dtd' => '2025-09-03'],
            ['source_no' => 3, 'no_so_booking' => '3100551942', 'no_quotation' => '1100226970', 'no_contract' => '2100157255', 'cargo_no_ka' => 'NMRTHD58E402R1S', 'no_spb' => 'MHCNMR81HSJ132439', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG BARAT', 'destination' => 'SURABAYA', 'area' => 'JAWA TIMUR', 'terima_do' => '2025-09-03', 'keluar_dari_pdc' => '2025-09-03', 'at_ptd_dtd' => '2025-09-06'],
            ['source_no' => 4, 'no_so_booking' => '3100551943', 'no_quotation' => '1100226970', 'no_contract' => '2100157255', 'cargo_no_ka' => 'NMRTHD58E402R1S', 'no_spb' => 'MHCNMR81HSJ132438', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG BARAT', 'destination' => 'SURABAYA', 'area' => 'JAWA TIMUR', 'terima_do' => '2025-09-03', 'keluar_dari_pdc' => '2025-09-03', 'at_ptd_dtd' => '2025-09-06'],
            ['source_no' => 5, 'no_so_booking' => '3100578200', 'no_quotation' => '1100239761', 'no_contract' => '2100169147', 'cargo_no_ka' => 'NMRTHD58E402R1S', 'no_spb' => 'MHCNMR81LSJ128976', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG BARAT', 'destination' => 'CILEGON', 'area' => 'JABODETABEK', 'terima_do' => '2025-09-09', 'keluar_dari_pdc' => '2025-09-09', 'at_ptd_dtd' => '2025-09-10'],
            ['source_no' => 6, 'no_so_booking' => '3100578201', 'no_quotation' => '1100239761', 'no_contract' => '2100169147', 'cargo_no_ka' => 'NMRTHD58E402R1S', 'no_spb' => 'MHCNMR81LSJ128978', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG BARAT', 'destination' => 'CILEGON', 'area' => 'JABODETABEK', 'terima_do' => '2025-09-09', 'keluar_dari_pdc' => '2025-09-09', 'at_ptd_dtd' => '2025-09-10'],
            ['source_no' => 7, 'no_so_booking' => '3100552732', 'no_quotation' => '1100228249', 'no_contract' => '2100158454', 'cargo_no_ka' => 'NMRTHD58E402R1S', 'no_spb' => 'MHCNMR81LSJ128973', 'kategori_moda' => 'LT Darat', 'origin' => 'KARAWANG BARAT', 'destination' => 'CILEGON', 'area' => 'JABODETABEK', 'terima_do' => '2025-09-09', 'keluar_dari_pdc' => '2025-09-09', 'at_ptd_dtd' => '2025-09-10'],
        ];

        $lautRows = [
            ['source_no' => 4257, 'no_booking_dtp' => '3100553433', 'no_quotation_dtp' => '1100143638', 'no_contract_dtp' => '2100089254', 'cargo' => 'PHR54CBBE4001S', 'noka' => 'MHCPHR54CSJ570508', 'kategori_moda' => 'LT Laut', 'origin' => 'KARAWANG BARAT', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-12', 'keluar_dari_pdc' => '2025-09-12', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-12', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4258, 'no_booking_dtp' => '3100553434', 'no_quotation_dtp' => '1100143638', 'no_contract_dtp' => '2100089254', 'cargo' => 'PHR54CBBE4001S', 'noka' => 'MHCPHR54CSJ572080', 'kategori_moda' => 'LT Laut', 'origin' => 'KARAWANG BARAT', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-12', 'keluar_dari_pdc' => '2025-09-12', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-12', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4259, 'no_booking_dtp' => '3100553435', 'no_quotation_dtp' => '1100143638', 'no_contract_dtp' => '2100089254', 'cargo' => 'PHR54CBBE4001S', 'noka' => 'MHCPHR54CSJ570603', 'kategori_moda' => 'LT Laut', 'origin' => 'KARAWANG BARAT', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-12', 'keluar_dari_pdc' => '2025-09-12', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-12', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4260, 'no_booking_dtp' => '3100553436', 'no_quotation_dtp' => '1100217503', 'no_contract_dtp' => '2100148489', 'cargo' => 'NMRTL53E402R1S', 'noka' => 'MHCNMR81LSJ132498', 'kategori_moda' => 'LT Laut', 'origin' => 'KARAWANG TIMUR', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-12', 'keluar_dari_pdc' => '2025-09-12', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-12', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4261, 'no_booking_dtp' => '3100553437', 'no_quotation_dtp' => '1100217503', 'no_contract_dtp' => '2100148489', 'cargo' => 'NMRTL53E402R1S', 'noka' => 'MHCNMR81LSJ132534', 'kategori_moda' => 'LT Laut', 'origin' => 'KARAWANG TIMUR', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-12', 'keluar_dari_pdc' => '2025-09-12', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-12', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4262, 'no_booking_dtp' => '3100553438', 'no_quotation_dtp' => '1100228444', 'no_contract_dtp' => '2100158648', 'cargo' => 'NLRMSE46JBI09M', 'noka' => 'MHCNLR85ESJ003018', 'kategori_moda' => 'LT Laut', 'origin' => 'TANGERANG', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-15', 'keluar_dari_pdc' => '2025-09-15', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-15', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4263, 'no_booking_dtp' => '3100553439', 'no_quotation_dtp' => '1100228444', 'no_contract_dtp' => '2100158648', 'cargo' => 'NLRMSE46JBI09M', 'noka' => 'MHCNLR85ESJ003019', 'kategori_moda' => 'LT Laut', 'origin' => 'TANGERANG', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-15', 'keluar_dari_pdc' => '2025-09-15', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-15', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
            ['source_no' => 4264, 'no_booking_dtp' => '3100553440', 'no_quotation_dtp' => '1100228444', 'no_contract_dtp' => '2100158648', 'cargo' => 'NLRMSE46JBI09M', 'noka' => 'MHCNLR85ESJ003020', 'kategori_moda' => 'LT Laut', 'origin' => 'TANGERANG', 'destination' => 'MEDAN', 'tujuan_pengiriman' => 'SUMATERA', 'terima_do' => '2025-09-15', 'keluar_dari_pdc' => '2025-09-15', 'jenis_kapal' => 'RORO', 'at_storage_port' => '2025-09-15', 'atd_kapal_loading' => '2025-09-16', 'ata_kapal' => '2025-09-20', 'ata_storage_port_destination' => '2025-09-20', 'at_ptd_dtd' => '#VALUE!'],
        ];

        foreach ($daratRows as $row) {
            IsoDaratShipment::firstOrCreate(['source_no' => $row['source_no']], $row);
        }

        foreach ($lautRows as $row) {
            IsoLautShipment::firstOrCreate(['source_no' => $row['source_no']], $row);
        }
    }
}
