<?php
/**
 * @var array $importers Esta variable contiene la configuracion para desplegar los importadores
 */

?>
<div class="wrap h-100 container">
    <h1 class="mb-3"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <br>

    <div class="bg-light rounded h-100 p-5">
        <div class="alert alert-info mb-5">
            <?= __("No cierres el navegador ni actualices la página mientras se ejecuta un proceso.","bwi") ?>
        </div>
        <div class="row">
            <div class="col-md-4">
                <ul class="nav flex-column" id="importer-tabs">
                    <?php foreach ($importers as $key => $importer): ?>
                        <?php $href = "#tab-content-".$importer['type']."_".$importer['process_type']; ?>
                        <li class="nav-item">
                            <a href="<?= $href ?>" class="nav-link <?= $key == 0 ? "active" : ""; ?>" data-bs-toggle="tab" data-bs-target="<?= $href ?>">
                                <?= $importer['title'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-8">
                <div class="tab-content">
                    <?php $log_dir = wp_upload_dir()['basedir'] . "/lb_logs/"; ?>
                    <?php $log_url = wp_upload_dir()['baseurl'] . "/lb_logs/"; ?>
                    <?php foreach ($importers as $key => $importer): ?>
                        <div id="tab-content-<?= $importer['type']."_".$importer['process_type'] ?>" class="tab-pane fade <?=  $key == 0 ? "show active" : ""; ?>">
                            <form enctype="multipart/form-data"
                                  name="import-<?= $importer['type'] ?>-form"
                                  class="form-box import-ajax-form"
                                  method="post"
                                  action="<?php echo esc_url(admin_url()); ?>admin-post.php" id="import-products-form">
                                <input type="hidden" name="action" value="process_script">
                                <input type="hidden" name="type" value="<?= $importer['type'] ?>">
                                <input type="hidden" name="process_type" value="<?= $importer['process_type'] ?>">
                                <input type="hidden" name="log_file" value="<?= str_replace('.log', '', $importer['log_file']); ?>">
                                <input type="hidden" name="page" value="1">
                                <input type="hidden" name="ajax-nonce" id="ajax-nonce"
                                       value="<?php echo wp_create_nonce('ajax-nonce'); ?>">
                                <h4><?= $importer['title']; ?></h4>
                                <?php if(isset($importer['description']) && $importer['description']):?>
                                    <p><?= $importer['description']; ?></p>
                                <?php endif ?>

                                <?php if(isset($importer['fields'])): ?>
                                    <?php foreach($importer['fields'] as $field): ?>
                                        <?php if($field['type'] == "checkbox"): ?>
                                            <div class="form-group">
                                                <label for="<?=  $field['name']."_".$key ?>">
                                                    <input type="checkbox" value="1"
                                                           name="<?= $field['name'] ?>"
                                                           id="<?=  $field['name']."_".$key ?>">
                                                    <?= __($field['label'], "lb") ?>
                                                </label>
                                                <?php if(isset($field['description'])):?>
                                                    <small><?= $field['description'] ?></small>
                                                <?php endif ?>
                                            </div>
                                        <?php endif ?>
                                        <?php if($field['type'] == "input"): ?>
                                            <div class="form-group">
                                                <label for="<?= $field['name']."_".$key ?>"><?= __($field['label'], "lb") ?></label>
                                                <input type="<?= $field['input_type'] ?? "text" ?>" name="<?= $field['name'] ?>" id="<?= $field['name']."_".$key ?>">
                                                <?php if(isset($field['description'])):?>
                                                    <small><?= $field['description'] ?></small>
                                                <?php endif ?>
                                            </div>
                                        <?php endif ?>
                                    <?php endforeach; ?>
                                <?php endif ?>

                                <?php if (file_exists($log_dir . $importer['log_file'])): ?>
                                    <div class="form-group">
                                        <a href="<?= $log_url . $importer['log_file'] ?>"
                                           download><?= __("Descargar logs", "bwi") ?></a>
                                    </div>
                                <?php endif ?>
                                <div class="form-group">
                                    <div class="bwi_process_info mb-5">
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bwi_progress_bar__fill" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                                        </div>
                                        <div class="bwi_process_info__stats">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <?= __("Iniciar", "bwi") ?>
                                    </button>
                                </div>
                                <div class="form-group">
                                    <div class="bwi_importer__log"></div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div
            </div>
        </div>
    </div>
</div>
