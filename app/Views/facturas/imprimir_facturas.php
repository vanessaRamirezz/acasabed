<?php

/** @var array $facturas */
/** @var bool $autoPrint */

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Impresión Facturas</title>

    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        .pagina {
            position: relative;
            width: 210mm;
            height: 148mm;
            page-break-after: always;
        }

        .comprobante {
            position: absolute;
            top: 17mm;
            width: 105mm;
            height: 148mm;
        }

        .izquierda {
            left: 0;
        }

        .derecha {
            left: 105mm;
        }

        .campo {
            position: absolute;
            font-weight: bold;
        }

        /* CAMPOS */
        .nombre {
            left: 15mm;
            top: 16mm;
            width: 78mm;
            white-space: nowrap;
            overflow: hidden;
        }

        .direccion {
            left: 15mm;
            top: 22mm;
            width: 70mm;
            word-break: break-word;
        }

        .documento {
            left: 82mm;
            top: 13mm;
            width: 18mm;
            text-align: center;
        }

        .cuenta {
            left: 82mm;
            top: 23mm;
            width: 18mm;
            text-align: center;
        }

        .medidor {
            left: 32mm;
            top: 40mm;
            width: 20mm;
            text-align: center;
        }

        .actual {
            left: 11mm;
            top: 50mm;
            width: 14mm;
            text-align: center;
        }

        .anterior {
            left: 31mm;
            top: 50mm;
            width: 14mm;
            text-align: center;
        }

        .consumo {
            left: 51mm;
            top: 50mm;
            width: 14mm;
            text-align: center;
        }

        .tarifa {
            left: 70mm;
            top: 50mm;
            width: 14mm;
            text-align: center;
        }

        .fecha {
            left: 85mm;
            top: 50mm;
            width: 18mm;
            text-align: center;
        }

        .detalle {
            position: absolute;
            left: 10mm;
            top: 55mm;
            /* ajuste único definitivo */
            width: 90mm;
        }

        /* FILAS */
        .d-codigo,
        .d-concepto,
        .d-valor {
            position: absolute;
            font-size: 10px;
        }

        /* columnas */
        .d-codigo {
            left: 0mm;
            width: 10mm;
        }

        .d-concepto {
            left: 10mm;
            width: 60mm;

            /* 🔥 ESTO ES LO IMPORTANTE */
            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;
            line-height: 2.3mm;
            /* controla separación entre líneas */
            font-weight: bold;
        }

        .d-valor {
            left: 70mm;
            width: 20mm;
            text-align: right;
            font-weight: bold;
        }

        /* TOTAL */
        .d-total-si-no-paga {
            position: absolute;
            top: 100mm;
            left: 60mm;
            /* MISMA columna que .d-valor */
            width: 6mm;
            text-align: right;
            font-weight: bold;
        }

        .d-total-value {
            position: absolute;
            top: 100mm;
            left: 95mm;
            /* 👈 segunda columna a la derecha */
            width: 5mm;
            text-align: right;
            font-weight: bold;
        }

        .d-fecha-vencimiento {
            position: absolute;
            top: 115mm;
            /* 👈 un poco debajo del total (100mm + margen) */
            left: 70mm;
            /* misma columna del total */
            width: 30mm;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php foreach ($facturas as $factura): ?>

        <div class="pagina">

            <?= view('facturas/comprobante', [
                'factura' => $factura,
                'lado' => 'izquierda'
            ]) ?>

            <?= view('facturas/comprobante', [
                'factura' => $factura,
                'lado' => 'derecha'
            ]) ?>

        </div>

    <?php endforeach; ?>

    <?php if ($autoPrint): ?>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>

</body>

</html>