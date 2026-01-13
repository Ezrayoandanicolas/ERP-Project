<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\SMBPrintConnector;

class ThermalPrintService
{
    public static function printSale($sale)
    {
        try {

            // ✅ AMAN SETELAH config:cache
            $type    = config('thermal.type');
            $printer = config('thermal.name');

            if (! $type) {
                throw new \Exception('THERMAL_PRINTER_TYPE empty');
            }

            $connector = null;

            switch ($type) {

                case 'cups':
                    $connector = new CupsPrintConnector($printer);
                    break;

                case 'smb':
                    $url   = $printer;
                    $host  = parse_url($url, PHP_URL_HOST);
                    $share = trim(parse_url($url, PHP_URL_PATH), '/');

                    if (! $host || ! $share) {
                        throw new \Exception('Invalid SMB printer URL');
                    }

                    $connector = new SMBPrintConnector($host, $share);
                    break;

                case 'windows':
                    $connector = new WindowsPrintConnector($printer);
                    break;

                case 'network':
                    $connector = new NetworkPrintConnector(
                        config('thermal.ip'),
                        config('thermal.port', 9100)
                    );
                    break;

                default:
                    throw new \Exception("Unknown THERMAL_PRINTER_TYPE: {$type}");
            }

            $printerObj = new Printer($connector);

            // ================= HEADER =================
            $store  = $sale->outlet->store->name ?? "Toko";
            $outlet = $sale->outlet?->name ?? "-";
            $admin  = auth()->user()->name ?? "Admin";

            $printerObj->setJustification(Printer::JUSTIFY_CENTER);
            $printerObj->text("$store\n");
            $printerObj->text("$outlet\n");
            $printerObj->text(str_repeat('-', 32) . "\n");

            $printerObj->setJustification(Printer::JUSTIFY_LEFT);
            $printerObj->text(self::lr("Tanggal", $sale->sale_date->format('d-m-Y')
 ?? now()->format('d-m-Y H:i')));
            $printerObj->text(self::lr("Order ID", $sale->id));
            $printerObj->text(self::lr("Kasir", $admin));
            $printerObj->text(self::lr("Customer", $sale->customer_name ?? "Umum"));

            $printerObj->text(str_repeat('-', 32) . "\n");

            // ================= PAYMENT =================
            $printerObj->setJustification(Printer::JUSTIFY_CENTER);
            $printerObj->text(strtoupper($sale->payment_method) . "\n");
            $printerObj->text(str_repeat('-', 32) . "\n");

            // ================= ITEMS =================
            $printerObj->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($sale->items as $item) {
                // 1. Ambil nama produk dasar
                $productName = $item->product->name;

                // 2. Tambahkan nama varian jika ada (misal: "Dimsum Menthai (Isi 4)")
                if ($item->variant) {
                    $productName = $item->variant->name;
                }

                $printerObj->text($productName . "\n");

                $left  = " {$item->qty}x @".number_format($item->price, 0, ',', '.');
                $right = number_format($item->subtotal, 0, ',', '.');

                $printerObj->text(self::lr($left, $right));
            }

            $printerObj->text(str_repeat('-', 32) . "\n");

            // ================= TOTAL =================
            $printerObj->text(self::lr("Subtotal",
                number_format($sale->items->sum('subtotal'), 0, ',', '.')
            ));

            $discount = $sale->discount > 0
                ? '-' . number_format($sale->discount, 0, ',', '.')
                : '0';

            $printerObj->text(self::lr("Discount", $discount));
            $printerObj->text(str_repeat('-', 32) . "\n");
            $printerObj->text(self::lr("TOTAL",
                number_format($sale->total, 0, ',', '.')
            ));

            // ================= FOOTER =================
            $printerObj->setJustification(Printer::JUSTIFY_CENTER);
            $printerObj->text("\nTerima kasih!\n\n");

            $printerObj->cut();
            $printerObj->close();

        } catch (\Throwable $e) {
            \Log::error("Thermal Error: " . $e->getMessage());
        }
    }

    private static function lr($label, $value, $width = 32)
    {
        $label = (string) $label;
        $value = (string) $value;

        $space = $width - strlen($label) - strlen($value);
        if ($space < 1) $space = 1;

        return $label . str_repeat(' ', $space) . $value . "\n";
    }
}
