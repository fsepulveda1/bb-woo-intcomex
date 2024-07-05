(function ($) {
    $(document).ready(function() {

        $(".import-ajax-form").on('submit', function(e) {
            e.preventDefault();

            window.onbeforeunload = confirmExit;
            function confirmExit() {
                return "¿Estás seguro de cerrar la página?, el proceso aún no ha terminado";
            }

            const form = $(this);
            const allForms = $('form.import-ajax-form');
            form.find('input[name="page"]').val('1');
            const formData = form.serialize()+"&ajax_nonce="+bwi_ajax_values.ajax_nonce;
            const stats = form.find('.bwi_process_info__stats');

            stats.text('Cargando...')
            form.find('.bwi_progress_bar__fill').css('width','0');
            form.find('.bwi_progress_bar__fill').addClass('progress-bar-animated');
            form.find('.bwi_progress_bar__fill').removeClass('bg-success');
            allForms.find('button[type="submit"]').attr('disabled',true)
            form.find('input[type="checkbox"]').attr('disabled',true);
            form.find('input[type="text"]').attr('disabled',true);
            form.find('input[type="number"]').attr('disabled',true);
            form.find('input[type="radio"]').attr('disabled',true);
            form.find('select').attr('disabled',true);
            $('#importer-tabs').addClass('disabled');
            form.find('.bwi_importer__log').html('');

            processScript(formData,form);
        });

        function processScript(formData, form) {
            const stats = form.find('.bwi_process_info__stats');
            const progressBar = form.find('.bwi_progress_bar__fill');
            const allForms = $('form.import-ajax-form');
            const importerTabs = $('#importer-tabs');

            $.ajax({
                type: "POST",
                url: bwi_ajax_values.ajax_url,
                data: formData,
                timeout: 600000,
                success: function (data) {

                    let errors = [];
                    if(data?.errors !== undefined) {
                        errors = JSON.parse(data?.errors);
                    }

                    if(errors && errors.length) {
                        const logWrapper = form.find('.bwi_importer__log')
                        var log = logWrapper.find('ol');
                        if(!log.length) {
                            logWrapper.append('<ol></ol>')
                            log = logWrapper.find('ol')
                        }
                        for (let i = 0; i < errors.length; i++) {
                            log.append('<li>' + errors[i] + '</li>');
                        }

                    }

                    if(form.find('input[name="process_type"]').val() === "single") {
                        switch (data.result) {
                            case 'COMPLETE':
                                progressBar.width('100%');
                                window.onbeforeunload = null;
                                break;
                            case 'ERROR':
                                progressBar.width(0);
                                window.onbeforeunload = null;
                                break;
                        }

                        allForms.find('button[type="submit"]').attr('disabled',false);
                        form.find('input').attr('disabled',false);
                        form.find('select').attr('disabled',false);
                        importerTabs.removeClass('disabled');


                        stats.text(data.message);
                    }

                    if(form.find('input[name="process_type"]').val() === "batch") {
                        const newFormData = JSON.parse(data.form_data);
                        switch (data.result) {
                            case 'COMPLETE':
                                progressBar.width('100%');
                                progressBar.removeClass('progress-bar-animated');
                                progressBar.addClass('bg-success');
                                stats.html(`100%<br> ${newFormData.current_row} de ${newFormData.total_rows} items procesados.`);
                                allForms.find('button[type="submit"]').attr('disabled',false);
                                form.find('input').attr('disabled',false);
                                form.find('select').attr('disabled',false);
                                importerTabs.removeClass('disabled');
                                window.onbeforeunload = null;
                                break;
                            case 'NEXT':
                                form.find("input[name='page']").val(newFormData.page);
                                let percent = ((parseInt(newFormData.page)-1) * 100) / parseInt(newFormData.total_pages);
                                stats.html(`${Math.round(percent)}%<br> ${newFormData.current_row} de ${newFormData.total_rows} items procesados.`)
                                progressBar.width(Math.round(percent)+"%");
                                processScript(newFormData,form);
                                break;
                            case 'FAIL':
                                progressBar.width(0);
                                stats.text("Ha ocurrido un error");
                                allForms.find('button[type="submit"]').attr('disabled',false)
                                form.find('input').attr('disabled',false);
                                form.find('select').attr('disabled',false);
                                importerTabs.removeClass('disabled');
                                window.onbeforeunload = null;

                                break;
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    progressBar.width(0);
                    jqXHR.abort();
                    if(textStatus==="timeout") {
                        stats.text("El servidor ha tardado mucho en responder");
                    }
                    else {
                        stats.text("Ha ocurrido un error");
                    }
                    allForms.find('button[type="submit"]').attr('disabled',false)
                }
            });
        }

        const datatables = $('.datatables');
        if(datatables.length > 0)
            datatables.DataTable({
                "language": {
                    "decimal": ",",
                    "thousands": ".",
                    "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "infoPostFix": "",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "loadingRecords": "Cargando...",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "searchPlaceholder": "Término de búsqueda",
                    "zeroRecords": "No se encontraron resultados",
                    "emptyTable": "Ningún dato disponible en esta tabla",
                    "aria": {
                        "sortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    //only works for built-in buttons, not for custom buttons
                    "buttons": {
                        "create": "Nuevo",
                        "edit": "Cambiar",
                        "remove": "Borrar",
                        "copy": "Copiar",
                        "csv": "fichero CSV",
                        "excel": "tabla Excel",
                        "pdf": "documento PDF",
                        "print": "Imprimir",
                        "colvis": "Visibilidad columnas",
                        "collection": "Colección",
                        "upload": "Seleccione fichero...."
                    },
                    "select": {
                        "rows": {
                            _: '%d filas seleccionadas',
                            0: 'clic fila para seleccionar',
                            1: 'una fila seleccionada'
                        }
                    }
                }
            });

        $('.add-discount-rule-link').on('click', function(e) {
            e.preventDefault();
            const modal = $('#modal-add-discount-rule');
            modal.find('input[name="discount_rule_id"]').val('');
            modal.find('select[name="category"]').val('');
            modal.find('input[name="discount_value"]').val('');
            modal.find('input[name="quantity_min"]').val('');
            modal.find('input[name="quantity_max"]').val('');

            modal.modal('show');
        });

        $('.edit-discount-rule-link').on('click', function (e) {
            e.preventDefault();
            const modal = $('#modal-add-discount-rule');
            const values = $(this).data('values');

            modal.find('input[name="discount_rule_id"]').val(values.id);
            modal.find('select[name="category"]').val(values.rule_value);
            modal.find('select[name="discount_type"]').val(values.discount_type);
            modal.find('input[name="discount_value"]').val(values.discount_value);
            modal.find('input[name="quantity_min"]').val(values.qty_min);
            modal.find('input[name="quantity_max"]').val(values.qty_max);

            modal.modal('show');
        })

    });
}(jQuery));
