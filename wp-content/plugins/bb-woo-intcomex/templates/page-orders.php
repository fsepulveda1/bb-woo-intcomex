<div class="wrap h-100 container" id="bwi-page">
    <h1 class="mb-3"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <br>

    <div class="bg-light rounded h-100 p-5">
        <div class="row">
            <div class="col-md-4">
                <ul class="nav flex-column" id="importer-tabs">
                    <?php $count = 0; foreach($tabs as $key => $tab): $count ++;?>
                        <li class="nav-item">
                            <a href="#" class="nav-link <?= $count == 1 ? "active" : "" ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $key ?>">
                                <?= __($tab['title'],"bwi") ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-8">
                <div class="tab-content">
                    <?php $count = 0; foreach($tabs as $key => $tab): $count ++;?>
                        <div id="tab-<?= $key ?>" class="tab-pane fade <?= $count == 1 ? "show active" : "" ?>">
                            <h4><?= __($tab['title'],"bwi"); ?></h4>
                            <p><?= __($tab['description'],"bwi"); ?></p>
                            <form action="" class="get-order-form" method="POST">
                                <input type="hidden" name="action" value="bwi_get_intcomex_order">
                                <input type="hidden" name="type" value="<?= $key ?>">
                                <div class="form-group">
                                    <label class="d-block" for="order_number"><?= __("N° de pedido","bwi"); ?> </label>
                                    <input type="text" name="order_number" id="order_number" class="">
                                    <small></small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <?= __("Consultar", "bwi") ?>
                                </button>
                                <div class="form-results"></div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
