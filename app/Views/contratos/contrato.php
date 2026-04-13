<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 1.5cm 1cm 1.5cm 1cm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 14.3px;
            line-height: 1.5;
            text-align: justify;
        }

        .numero {
            text-align: right;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .campo {
            display: inline-block;
            min-width: 120px;
            border-bottom: 1px solid #000;
            text-align: center;
            line-height: 12px;
        }

        /* tamaños específicos */
        .corto {
            min-width: 150px;
        }

        .largo {
            min-width: 300px;
            margin-top: 13px;
        }

        .titulo1 {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
        }

        p {
            font-weight: normal;
        }

        strong {
            font-weight: bold;
        }

        .seccion {
            margin-top: 15px;
        }

        .firma-container {
            margin-top: 40px;
            width: 100%;
            text-align: center;
            font-size: 0;
            /* 🔥 elimina espacios entre inline-block */
        }

        .firma {
            width: 35%;
            display: inline-block;
            text-align: center;
            margin: 20px 5%;
            vertical-align: top;
            /* 🔥 alinea correctamente */
            font-size: 14px;
            /* restaura texto */
        }

        .linea {
            border-top: 1px solid #000;
            width: 85%;
            margin: 0 auto 10px auto;
        }

        .nombre {
            font-weight: normal;
            margin-bottom: 4px;
            min-height: 20px;
            /* 🔥 evita que nombres cortos desalineen */
        }

        .titulo {
            font-size: 12px;
            font-weight: normal;
        }
    </style>
</head>

<body>

    <div class="numero">
        N° CONTRATO TO BILLING WATER:
        <span class="campo">
            <?= $numeroContrato ?? '' ?>
        </span>
    </div>

    <div class="titulo1">
        CONTRATO DE ACOMETIDA ENTRE USUARIO/A Y LA JUNTA DIRECTIVA DE LA ASOCIACION
        COMUNAL ADMINISTRADORA DEL SISTEMA DE AGUA POTABLE DEL CASERÍO EL COYOLITO,
        CANTÓN QUITASOL, DEL MUNICIPIO DE TEJUTLA DEPARTAMENTO DE CHALATENANGO.
        BENDICIÓN DE DIOS (ACASABED)
    </div>

    <p>
        <span>
            <strong>DOLORES FRANCO MENJIVAR</strong>, pescador, de <?= $edadRepresentante ?? '' ?> años de edad, del domicilio del Caserío El Coyolito de
            Cantón Quitasol del Municipio de Tejutla del Departamento de Chalatenango, con documento único de
            identidad personal número 0144459-1, actuando en calidad de representante legal de la ASOCIACION
            COMUNAL ADMINISTRADORA DEL SISTEMA DE AGUA POTABLE DEL CANTÓN EL COYOLITO DEL
            MUNICIPIO DE TEJUTLA Y DEPARTAMENTO DE CHALATENANGO. BENDICION DE DIOS. (ACASABED)
            con publicación en DIARIO OFICIAL de fecha viernes 12 de Julio 2013, NUMERO 128 TOMO NUMERO
            400, Con NIT numero 0433-140613-101-8, y que en el curso del presente CONTRATO se denominara la
            ASOCIACION y <span class="campo largo"><?= $nombre ?? '' ?></span>, de
            <span class="campo corto"><?= $edad ?? '' ?></span> años de edad del domicilio de Tejutla del Departamento de Chalatenango, con
            Documento Único de Identidad personal número <span class="campo corto"><?= $dui ?? '' ?></span>, que en este
            documento se denominará El/La USUARIO /A. Por este medio hacemos constar que hemos acordado
            celebrar el presente CONTRATO, mediante el cual como El/La USUARIO/A del servicio de agua, que soy
            me comprometo a responsabilizarme del cuidado de la acometida domiciliar que LA ASOCIACION,
            representada por el primero que comparece, y que me brinda desde, noviembre del año dos mil once. El
            cual consta de las siguientes cláusulas:
        </span>
    </p>

    <div class="seccion">
        <strong>RESPONSABILIDADES DE LA ASOCIACIÓN:</strong>
    </div>

    <ol type="I">
        <li>
            Después de haber cumplido los requisitos estipulados en el Reglamento Interno para ser
            Usuario/a del servicio de agua, como parte de los componentes de la acometida se le instalo el
            medidor en el lindero de su terreno y es responsabilidad del usuario, colocar los accesorios y
            tubería necesaria hasta ka colocación del grifo. (haciendo excepciones en algunos casos referente a
            la colocación del medidor)
        </li>
        <li>
            Velar porque el Usuario/a se le suministre el servicio de agua en el lugar de residencia el cual es
            propietario
        </li>
        <li>
            Darle el mantenimiento necesario a la red de distribución hasta antes del medidor y en general a
            todo el sistema de agua
        </li>
    </ol>

    <p>
        <strong>JUNTA DIRECTIVA DE LA ASOCIACION:</strong> organismo que queda facultado y reconocido por el USUARIO
        para que tome las disposiciones relacionadas con la administración, operación y mantenimiento del
        sistema, siempre y cuando estas no sean atentatorias a este convenio, normas de la ASOCIACION o a las
        Leyes de la República
    </p>

    <div class="seccion">
        <strong>FACULTADES DE LA JUNTA DIRECTIVA:</strong>
    </div>

    <ol type="I">
        <li>
            Dictar las normas necesarias para la utilización racional del recurso agua.
        </li>
        <li>
            Establecer las tarifas de cobro por el servicio de agua, de acuerdo al Reglamento Interno de
            Administración del Sistema de Agua.
        </li>
        <li>
            El cobro por la prestación del servicio y por el mantenimiento del sistema.
        </li>
        <li>
            Cobro por reconexión, mora, reposición de recibo, daños ocasionados por el usuario.
        </li>


        <div style="page-break-before: always;"></div>

        <li>
            La imposición de multas, e inclusive la potestad de establecer las normas necesarias que privarár
            a efecto de que los usuarios hagan un uso racional del agua.
        </li>
        <li>
            Facultad de suspender el servicio en aquellos casos que lo ameriten, en aras de garantizar la
            sostenibilidad del mismo.
        </li>
        <li>
            Establecer los horarios de prestación del servicio de agua, cuando sea necesario.
        </li>
        <li>
            Imponer las sanciones necesarias por incumplimiento al presente contrato por parte del
            USUARIO, de acuerdo al Reglamento Interno respectivo.
        </li>
        <li>
            Convocar al Usuario/a a cualquier reunión con no menos de tres días de anticipación para tratar
            asuntos de interés sobre el sistema de agua.
        </li>
    </ol>

    <p>
        <strong>USUARIO.</strong> Persona beneficiaria del servicio de agua que presta la asociación y como usuario me
        comprometo a cumplir las disposiciones establecidas en los estatutos reglamento interno y otras leyes de
        la República aplicables, para poder tener derecho al servicio de agua. Si no ha cumplido los requisitos
        para el servicio de agua, tendrá que llenar una solicitud de acometida donde se establecen los
        compromisos para tener derecho al servicio.
    </p>

    <div class="seccion">
        <strong>DERECHOS DEL USUARIO/A</strong>
    </div>
    <ol type="a">
        <li>
            Ser admitido(a) como Asociado(a) cuando haya presentado solicitud ante la Junta Directiva y
            cumpla los requisitos establecidos en Art.6 de este Reglamento.
        </li>
        <li>
            Ser suscriptor de un contrato individual de servicio de abastecimiento de agua potable.
        </li>
        <li>
            Recibir el servicio de agua en iguales condiciones que el prestado a los asociados.
        </li>
        <li>
            Solicitar y recibir oportunamente, información de su situación como cliente.
        </li>
        <li>
            Hacer reclamos a la administración de La Asociación por insatisfacción en el servicio de agua que
            recibe.
        </li>
        <li>
            Todo cliente tiene derecho a renunciar a su derecho de acometida, siempre y cuando lo solicite a la
            Junta Directiva para que esta proceda a retirar el medidor de su vivienda. Esta acción no
            compromete a la Junta Directiva a hacer devolución de dinero al cliente renunciante.
        </li>
        <li>
            Todo cliente que solicite su retiro deberá estar solvente de cualquier monto adeudado a la
            Asociación.
        </li>
    </ol>

    <p>
        El cliente que haya renunciado puede volver a incorporarse como cliente siempre que lo solicite a la Junta
        Directiva y cancele los costos de los trámites y trabajos a realizar para su reincorporación. Este ordinal
        queda sujeto a disposición de derechos de agua disponible. El cual al momento de la renuncia lo perdió
    </p>

    <div class="seccion">
        <strong>OBLIGACIONES DEL USUARIO.</strong>
    </div>
    <ol type="I">
        <li>
            Permitir que los empalmes de tubería y trabajos de fontanería sean realizados por un fontanero o
            personal autorizado por la Junta.
        </li>
        <li>
            Acepto que el trabajo realizado y el que se pueda realizar en un futuro para el mejoramiento del
            sistema de agua, es en beneficio de la comunidad y de todos los usuarios, y es de exclusiva
            propiedad de los socios que firmaron el acta de constitución y me someteré a las decisiones que
            ellos tomen siempre que sean en beneficio del sistema de agua, el cual está representado por la
            Junta Directiva Administradora y Junta de Vigilancia. Por lo tanto la Junta Directiva
            Administradora tiene el deber de administrar los recursos de una forma adecuada. A la vez se
            reserva el derecho de otorgar nuevas acometidas, ampliaciones de acuerdo al diseño del sistema.
        </li>


        <div style="page-break-before: always;"></div>
        <li>
            Me comprometo también a cancelar puntualmente, la tarifa por servicio de agua mas el uso de red,
            según los bloques de consumo para uso domiciliar, que se estipula en el Reglamento Interno. De
            acuerdo a los gastos mínimos requeridos para el buen funcionamiento.
        </li>
        <li>
            acepto que no debo vender mi derecho, ni hacer derivaciones de tubería de mi servicio a otra
            propiedad sin que la Junta Directiva me lo apruebe y me doy por enterado que si incumplo este
            acuerdo seré acreedor a una sanción ya sea económica o verbal y dependiendo la gravedad del
            caso hasta la suspensión del servicio. Igual se aplica para el hurto de agua específicamente.
        </li>
        <li>
            Me comprometo hacer un uso racional del agua y de los recursos naturales.
        </li>
        <li>
            Estoy dispuesto a recibir y acatar las instrucciones verbales y escritas que para el buen uso del
            servicio, me hiciere el personal de la Junta Directiva y Empleados.
        </li>
        <li>
            Permitiré el acceso a Junta Directiva y Empleados, las veces que sean necesarias para la revisión
            de la acometida, para el uso adecuado del sistema de agua.
        </li>
        <li>
            Soy responsable de darle el mantenimiento necesario a las instalaciones internas, evitando fugas y
            otros daños que perjudiquen al sistema de agua.
        </li>
        <li>
            Hare custodia de las tuberías que pasan frente a mi vivienda, haciendo participe a la Junta
            Directiva y Empleados, de cualquier amenaza o daño que atente contra los acueductos.
        </li>
        <li>
            Atenderé fielmente las instrucciones emanadas de la Junta Directiva, para la conservación del
            medio ambiente, aguas servidas y cualquier otro factor que fuere señalado con relación al uso
            racional y mejoramiento de la salud de mi familia.
        </li>
        <li>
            Motivare a mis vecinos a darle apoyo a la Junta Directiva y Junta de Vigilancia para asegurar la
            auto sostenibilidad del proyecto.
        </li>
    </ol>

    <p>
        <strong>EL COSTO.</strong> El costo de esta acometida fue de <span"><?= $montoTexto ?? '' ?></span> exactos ($<span"><?= $montoNumero ?? '' ?></span>) por haber estado en el
                listado de beneficiarios desde inicios del ejecución del proyecto.
    </p>

    <div class="seccion">
        <strong>DISPOSICIONES GENERALES:</strong>
    </div>
    <ol type="I">
        <li>
            Este convenio es por tiempo indefinido, siempre y cuando no se violen los acuerdos suscritos.
        </li>
        <li>
            La Junta Directiva y Junta de Vigilancia, se reserva el derecho de modificar las clausulas
            estipuladas en este convenio, informando al usuario/a previamente de dichos cambios.
            Tanto la persona representante legal de la Asociación como el usuario, nos damos por enterados/as y
            ratificamos el contenido de este convenio, el cual firmamos dando FE de nuestra conformidad, en la
            oficina de la Administrativa del sistema de agua ubicada en Plaza comercial don Yon Caserío el Coyolito,
            Cantón Quitasol del Municipio Tejutla, del Departamento de Chalatenango a las once horas del día veinte
            de diciembre del año dos mil catorce.
        </li>
    </ol>

    <div class="firma-container">

        <!-- Usuario -->
        <div class="firma">
            <div class="linea"></div>
            <div class="nombre"><?= $nombre ?? '' ?></div>
            <div class="titulo">Firma Usuario</div>
        </div>

        <!-- Firmante 1 -->
        <?php if (!empty($nombreFirmante1) || !empty($rolFirmante1)) : ?>
            <div class="firma">
                <div class="linea"></div>
                <div class="nombre"><?= $nombreFirmante1 ?></div>
                <div class="titulo"><?= $rolFirmante1 ?></div>
            </div>
        <?php endif; ?>

        <!-- Firmante 2 -->
        <?php if (!empty($nombreFirmante2) || !empty($rolFirmante2)) : ?>
            <div class="firma">
                <div class="linea"></div>
                <div class="nombre"><?= $nombreFirmante2 ?></div>
                <div class="titulo"><?= $rolFirmante2 ?></div>
            </div>
        <?php endif; ?>

        <!-- Firmante 3 -->
        <?php if (!empty($nombreFirmante3) || !empty($rolFirmante3)) : ?>
            <div class="firma">
                <div class="linea"></div>
                <div class="nombre"><?= $nombreFirmante3 ?></div>
                <div class="titulo"><?= $rolFirmante3 ?></div>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>