<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class ThermalPrintService
{
    public static function printSale($sale)
    {
        try {
            // pilih konektor
            if (env('THERMAL_PRINTER_TYPE') === 'windows') {
                $connector = new WindowsPrintConnector(env('THERMAL_PRINTER'));
            } else {
                $connector = new NetworkPrintConnector(
                    env('THERMAL_PRINTER_IP'),
                    9100
                );
            }

            $printer = new Printer($connector);

            $store = $sale->outlet->store->name ?? "Toko";
            $outlet = $sale->outlet?->name ?? "-";
            $admin = auth()->user()->name ?? "Admin";

            // ======================================
            // HEADER
            // ======================================
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            // $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_EMPHASIZED);
            $printer->text("$store\n");         // Induk Toko
            $printer->selectPrintMode(); // normal
            $printer->text("$outlet\n");
            $printer->text("-----------------------------\n");

            // Tanggal
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            $printer->text(self::lr("Tanggal", date('d-m-Y H:i')));
            $printer->text(self::lr("Order ID", $sale->id));
            $printer->text(self::lr("Collected by", $admin));

            $printer->text("-----------------------------\n");


            // ======================================
            // PAYMENT METHOD
            // ======================================
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(strtoupper($sale->payment_method) . "\n");
            $printer->text("-----------------------------\n");

            // ======================================
            // ITEM LIST
            // ======================================
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($sale->items as $item) {
                $name = $item->product->name;
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($name . "\n");

                $qty = "{$item->qty}x";
                $price = number_format($item->price, 0, ',', '.');
                $subtotal = number_format($item->subtotal, 0, ',', '.');

                // kiri: " 1x @10.000"
                $left = " " . str_pad($qty, 3) . " @" . $price;

                // kanan: subtotal
                $right = $subtotal;

                $printer->text(self::lr($left, $right));
            }


            $printer->text("-----------------------------\n");

            // ======================================
            // SUBTOTAL + DISCOUNT + TOTAL
            // ======================================
            $subtotal = number_format($sale->items->sum('subtotal'), 0, ',', '.');
            $discount = number_format($sale->discount, 0, ',', '.');
            $total    = number_format($sale->total, 0, ',', '.');

            $printer->text(self::lr("Subtotal", $subtotal));

            $discountValue = $sale->discount > 0 ? "-" . $discount : "0";
            $printer->text(self::lr("Discount", $discountValue));

            $printer->text("-----------------------------\n");

            // TOTAL (DOUBLE WIDTH)
            // Baris total RAPI kiri–kanan (normal width)
            $printer->text(self::lr("TOTAL", $total));
            $printer->text("-----------------------------\n");

            // ======================================
            // FOOTER
            // ======================================
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("# $store #\n");
            $printer->text("Terima kasih!\n");
            $printer->text("\n\n");

            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            \Log::error("Thermal Error: " . $e->getMessage());
        }
    }

    private static function lr($label, $value, $width = 32)
    {
        $text = $label . ": ";
        $space = $width - strlen($text) - strlen($value);

        if ($space < 1) $space = 1;

        return $text . str_repeat(' ', $space) . $value . "\n";
    }

}
