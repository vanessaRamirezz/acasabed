<style>
    body {
        font-size: 10px;
    }

    .borde {
        border: 1px solid #000;
    }

    .titulo {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
    }

    .center {
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 3px;
    }
</style>

<!-- CONTENEDOR PRINCIPAL -->
<table class="borde">
    <tr>
        <td>

            <!-- ENCABEZADO -->
            <table>
                <tr>
                    <td width="20%">
                        <!-- aquí luego puedes meter logo -->
                    </td>
                    <td width="80%" class="center">
                        <strong>ACASABED</strong><br>
                        Asociación Comunal Administradora del Sistema de Agua<br>
                        <strong>COMPROBANTE DEL CLIENTE</strong>
                    </td>
                </tr>
            </table>

            <br>

            <!-- DATOS CLIENTE -->
            <table class="borde">
                <tr>
                    <td width="70%">
                        <strong><?= $factura['cliente'] ?></strong><br>
                        <?= $factura['numero_contrato'] ?>
                    </td>
                    <td width="30%" class="borde">
                        <strong>No. DOCUMENTO</strong><br>
                        <?= $factura['correlativo'] ?>
                    </td>
                </tr>
            </table>

            <br>

            <!-- MEDIDOR -->
            <table class="borde">
                <tr>
                    <td class="center">LECTURA ACTUAL</td>
                    <td class="center">LECTURA ANTERIOR</td>
                    <td class="center">CONSUMO</td>
                </tr>
                <tr>
                    <td class="center">--</td>
                    <td class="center">--</td>
                    <td class="center">--</td>
                </tr>
            </table>

            <br>

            <!-- DETALLE -->
            <table class="borde">
                <tr>
                    <td width="70%" class="center"><strong>CONCEPTO</strong></td>
                    <td width="30%" class="center"><strong>VALOR</strong></td>
                </tr>

                <?php foreach ($detalle as $item): ?>
                    <tr>
                        <td>
                            Cuota <?= $item['numero_cuota'] ?? '-' ?>
                        </td>
                        <td class="center">
                            $<?= number_format($item['monto_cuota'], 2) ?>
                        </td>
                    </tr>

                    <?php if ($item['recargo_aplicado'] > 0): ?>
                        <tr>
                            <td>Mora</td>
                            <td class="center">
                                $<?= number_format($item['recargo_aplicado'], 2) ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                <?php endforeach; ?>
            </table>

            <br>

            <!-- TOTALES -->
            <table class="borde">
                <tr>
                    <td>Total Cuotas</td>
                    <td class="center">$<?= number_format($factura['total_cuotas'], 2) ?></td>
                </tr>
                <tr>
                    <td>Total Mora</td>
                    <td class="center">$<?= number_format($factura['total_mora'], 2) ?></td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="center">
                        <strong>
                            $<?= number_format($factura['total_cuotas'] + $factura['total_mora'], 2) ?>
                        </strong>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>