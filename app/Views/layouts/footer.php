<!-- /.content-wrapper FOOTER DE LA PAGINA-->
<footer class="main-footer">
    <strong>Año <?php echo date("Y")?></strong>
    <!-- <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.0.0
    </div> -->
</footer>

<div class="modal fade" id="logoutModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">¿Listo para salir?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Seleccione "Salir" a continuación si está listo para finalizar su sesión actual..</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <!-- <button type="button" class="btn btn-primary">Salir</button> -->
                <a class="btn btn-primary" href="<?= base_url('salir') ?>">Salir</a>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->