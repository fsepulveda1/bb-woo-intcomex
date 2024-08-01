<div class="wrap h-100 container" id="bwi-page">
    <h1 class="mb-3"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <br>

    <div class="bg-light rounded h-100 p-5">
        <div class="row">
            <div class="col-md-4">
                <ul class="nav flex-column" id="importer-tabs">

                    <li class="nav-item">
                        <a href="#" class="nav-link active" data-bs-toggle="tab" data-bs-target="tab-get-order">
                            <?= __("Consultar pedidos","bwi") ?>
                        </a>
                    </li>

                </ul>
            </div>
            <div class="col-md-8">
                <div class="tab-content">
                    <div id="tab-get-order" class="tab-pane fade show active">
                        <h4><?= __("Consultar pedidos de Intcomex","bwi"); ?></h4>
                        <p><?= __("Ingresa el número de pedido y presiona el botón \"consultar\" para obtener un detalle del pedido en Intcomex.","bwi"); ?></p>
                        <form action="" class="get-order-form" method="POST">
                            <input type="hidden" name="action" value="bwi_get_intcomex_order">
                            <div class="form-group">
                                <label class="d-block" for="order_number"><?= __("N° de pedido","bwi"); ?> </label>
                                <input type="text" name="order_number" id="order_number" class="">
                                <small></small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?= __("Consultar", "bwi") ?>
                            </button>
                        </form>

                        <div id="get-order-results"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
