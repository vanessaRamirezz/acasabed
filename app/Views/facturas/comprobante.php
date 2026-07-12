<?php

/** @var array $factura */
/** @var string $lado */

$lado = $lado ?? 'izquierda';
?>

<div class="comprobante <?= $lado ?>">

    <div class="campo nombre">
        <?= esc($factura['cliente']) ?>
    </div>

    <div class="campo direccion">
        <?= esc($factura['direccion']) ?>
    </div>

    <div class="campo documento">
        <?= esc($factura['numero_contrato']) ?>
    </div>

    <div class="campo cuenta">
        <?= esc($factura['ficha_alcaldia']) ?>
    </div>

    <div class="campo medidor">
        <?= esc($factura['numero_serie']) ?>
    </div>

    <div class="campo actual">
        <?= esc($factura['lecturaActual']) ?>
    </div>

    <div class="campo anterior">
        <?= esc($factura['lecturaAnterior']) ?>
    </div>

    <div class="campo consumo">
        <?= esc($factura['consumo']) ?>
    </div>

    <div class="campo tarifa">
        <?= esc($factura['codigoTarifa']) ?>
    </div>

    <div class="campo fecha">
        <?= esc($factura['fechaLectura']) ?>
    </div>


    <div class="detalle">

        <?php
        $yBase = 0;
        $y = 0;

        $total = 0;
        $totalSinMora = 0;
        ?>

        <?php foreach ($factura['detalle'] as $i => $item): ?>

            <?php
            $concepto = $item['concepto'] ?? '';
            $valor = ($item['monto'] ?? 0) + ($item['mora'] ?? 0);

            $total += $valor;
            $totalSinMora += ($item['monto'] ?? 0);

            $y = $yBase + (7 * ($i + 1));
            ?>

            <!-- CÓDIGO (si no lo usas, lo puedes dejar vacío o eliminar CSS) -->
            <div class="d-codigo" style="top: <?= $y ?>mm;"></div>

            <!-- CONCEPTO (con salto de línea automático) -->
            <div class="d-concepto" style="top: <?= $y ?>mm;">
                <?= esc($concepto) ?>
            </div>

            <!-- VALOR -->
            <div class="d-valor" style="top: <?= $y ?>mm;">
                <?= number_format($valor, 2) ?>
            </div>

        <?php endforeach; ?>

        <!-- RELLENO HASTA 10 FILAS -->
        <?php for ($i = count($factura['detalle']); $i < 10; $i++): ?>

            <?php $y = $yBase + (7 * ($i + 1)); ?>

            <div class="d-codigo" style="top: <?= $y ?>mm;"></div>
            <div class="d-concepto" style="top: <?= $y ?>mm;"></div>
            <div class="d-valor" style="top: <?= $y ?>mm;"></div>

        <?php endfor; ?>
    </div>

    <!-- TOTAL -->
    <?php
    $totalConMora = $total + 2;
    $totalConMoraFormateado = number_format($totalConMora, 2);
    ?>
    <div class="d-total-si-no-paga"><?= number_format($totalConMoraFormateado, 2) ?></div>
    <div class="d-total-value"><?= number_format($total, 2) ?></div>
    <div class="d-fecha-vencimiento"><?= esc($factura['fechaVencimiento']) ?></div>
</div>