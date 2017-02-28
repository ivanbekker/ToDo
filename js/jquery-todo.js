/**
 * @name jQuery simple TODO lists plugin
 * @author Dmitry Simonov (best-play)
 */
(function ($) {
    $.fn.TODO = function (options) {
        var defaults = {
            add_project_btn_name: 'Add TODO List'
        };
        var opts = $.extend(defaults, options);

        var main_obj = $(this);

        return init();

        /**
         * initialize plugin
         */
        function init() {
            createInfoBoxes();
            loadProjects();
        }

        /**
         * Loading projects and tasks via ajax from server
         */
        function loadProjects() {
            $.blockUI({
                message: $('#blockLayer'),
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: 0
                }
            });

            $.ajax({
                url: 'api/action.php?action=get',
                cache: false,
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        for (var project in data.projects) {
                            main_obj
                                // Добавление header
                                .append($('<div class="panel panel-primary"></div>')
                                    .append($('<div class="panel-heading header-height"></div>')
                                        .append($('<div class="panel-title"></div>')
                                            .append($('<div class="row"></div>')
                                                .append($('<div class="col-lg-1 text-center opacity"></div>')
                                                    .append($('<span class="fa fa-list-alt fa-lg">'))
                                            )
                                                .append($('<div class="col-lg-9 no-left-padding"></div>').html(data.projects[project].name))
                                                .append($('<div class="col-lg-2 text-right opacity"></div>')
                                                    .append($('<i class="fa fa-pencil fa-lg edit_project style-cursor"></i>').data('project_id', data.projects[project].id))
                                                    .append($('<span class="separator"></span>'))
                                                    .append($('<i class="fa fa-trash fa-lg delete_project style-cursor"></i>').data('project_id', data.projects[project].id))
                                            )
                                        )
                                    )
                                )
                                    // Добавление формы добавления таска
                                    .append($('<div class="add_project"></div>')
                                        .append($('<div class="input-group"></div>')
                                            .append($('<span class="input-group-addon plus"></span>')
                                                .append($('<i class="fa fa-plus"></i>'))
                                        )
                                            .append($('<input class="form-control">').attr({
                                                type: 'text',
                                                placeholder: 'Start typing here to create a task'
                                            }))
                                            .append($('<span class="input-group-btn"></span>')
                                                .append($('<button class="btn btn-success add_task"></button>').attr('type', 'button').html('Add Task').data('project_id', data.projects[project].id)
                                            )
                                        )
                                    )
                                )
                                    // Добавление таблицы с тасками
                                    .append($('<div class="panel-body"></div>')
                                        .append($('<table class="custom-bordered-table table-hover"></table>'))
                                )
                            );

                            for (var task in data.tasks) {
                                if (data.tasks[task].project_id === data.projects[project].id) {
                                    // Заполняем таблицу
                                    main_obj.find('table:last')
                                        .append($('<tr data-priority="priority_' + data.tasks[task].task_id + '"></tr>')
                                            .append($('<td class="task-status"></td>')
                                                .append($('<i class="fa fa-square-o style-cursor change_task_status"></i>').data('task_id', data.tasks[task].task_id)))
                                            .append($('<td class="task-description"></td>').html(data.tasks[task].descr))
                                            .append($('<td class="controls"></td>')
                                                .append($('<div class="main_controls"></div>')
                                                    .append($('<i class="fa fa-arrows arrows-task style-cursor"></i>').data('task_id', data.tasks[task].task_id))
                                                    .append($('<span class="separator"></span>'))
                                                    .append($('<i class="fa fa-pencil edit_task style-cursor"></i>').data('task_id', data.tasks[task].task_id))
                                                    .append($('<span class="separator"></span>'))
                                                    .append($('<i class="fa fa-trash delete_task style-cursor"></i>').data('task_id', data.tasks[task].task_id)
                                                )
                                            )
                                                .append($('<div class="edit_controls"></div>')
                                                    .append($('<i class="fa fa-check edit_task_btn_action style-cursor"></i>'))
                                                    .append($('<span class="separator"></span>'))
                                                    .append($('<i class="fa fa-times cancel_task_btn_action style-cursor"></i>')
                                                )
                                            )
                                        )
                                    );

                                    // Если таск выполнен, то ставим галку
                                    if (data.tasks[task].status === '1') {
                                        main_obj.find('.task-status i:last').removeClass('fa-square-o').addClass('fa-check-square-o text-success');
                                    }
                                }
                            }

                            // добавляем возможность менять приоритет задачи
                            makeSortable(main_obj.find('table:last tbody'));
                        }

                        // добавляем кнопку для создание проекта
                        main_obj
                            .append($('<p class="text-center"></p>')
                                .append($('<button class="btn btn-primary btn-lg"></button>').attr({
                                    'type': 'button',
                                    'id': 'add_project_btn'
                                })
                                    .html('<i class="fa fa-plus"></i> <strong>' + opts.add_project_btn_name + '</strong>')
                            )
                        );

                        // разблокируем рабочую область после успешной загрузки
                        $.unblockUI();
                    }

                    // FIX BORDER-RADIUS
                    $('table').each(function () {
                        if (!$(this).find('tr').length)
                            $(this).closest('div.panel').css('border-radius', 0);
                    });

                    // Execute events
                    bindEvents();
                },
                error: function () {
                    $.unblockUI();
                    showError('Some uncaught error occurs during saving data');
                    return false;
                }
            });
        }

        function bindEvents() {
            // Добавление проекта
            main_obj.on('click', '#add_project_btn', function () {
                var btn = $(this);
                btn.prop('disabled', true);
                var addProjectForm = $('<div class="panel panel-primary"></div>').css('border-radius', 0);
                addProjectForm
                    .append($('<div class="panel-heading header-height">')
                        .append($('<div class="panel-title"></div>')
                            .append($('<div class="row"></div>')
                                .append($('<div class="col-lg-1 text-center opacity"></div>')
                                    .append($('<span class="fa fa-list-alt fa-lg"></span>'))
                            )
                                .append($('<div class="col-lg-11 no-left-padding"></div>')
                                    .append($('<div class="input-group"></div>')
                                        .append($('<input>').attr({
                                            type: 'text',
                                            class: 'form-control',
                                            placeholder: 'Start typing here to create a project'
                                        }))
                                        .append($('<span class="input-group-btn"></span>')
                                            .append($('<button class="btn btn-success add_project_btn_action"></button>').attr('type', 'button')
                                                .append($('<span class="fa fa-check"></span>')))
                                            .append($('<button class="btn btn-danger cancel_project_btn_action"></button>').attr('type', 'button')
                                                .append($('<span class="fa fa-times"></span>')))
                                    )
                                )
                            )
                        )
                    )
                );

                // Добавим на страницу
                main_obj.find(btn).closest('p').before(addProjectForm);

                // Обработчик для добавление проекта и отмены добавления
                $('.add_project_btn_action').on('click', function () {
                    var $_this = $(this);
                    var params, dataStr;
                    var input = $_this.closest('div').find('input');
                    params = {
                        'name': input.val().replace(/(<([^>]+)>)/ig, "").trim() // trim HTML tags
                    };
                    dataStr = $.param(params);

                    if (!params.name) {
                        showError('Название проекта не должно быть пустым!');
                        return false;
                    }

                    $.ajax({
                        url: 'api/action.php?action=save_project',
                        type: 'POST',
                        data: dataStr,
                        timeout: 30000,
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                var newRow = $_this.closest('div.row');
                                $_this.closest('div.no-left-padding').remove();
                                newRow
                                    .append($('<div class="col-lg-9 no-left-padding"></div>').html(params.name))
                                    .append($('<div class="col-lg-2 text-right opacity"></div>')
                                        .append($('<i class="fa fa-pencil fa-lg edit_project style-cursor"></i>').data('project_id', data.project_id))
                                        .append($('<span class="separator"></span>'))
                                        .append($('<i class="fa fa-trash fa-lg delete_project style-cursor"></i>').data('project_id', data.project_id))
                                );
                                addProjectForm
                                    .append($('<div class="add_project"></div>')
                                        .append($('<div class="input-group"></div>')
                                            .append($('<span class="input-group-addon plus"></span>')
                                                .append($('<i class="fa fa-plus"></i>'))
                                        )
                                            .append($('<input class="form-control">').attr({
                                                type: 'text',
                                                placeholder: 'Start typing here to create a task'
                                            }))
                                            .append($('<span class="input-group-btn"></span>')
                                                .append($('<button class="btn btn-success add_task"></button>').attr('type', 'button').html('Add Task').data('project_id', data.project_id)
                                            )
                                        )
                                    )
                                )
                                    .append($('<div class="panel-body"></div>')
                                        .append($('<table class="custom-bordered-table table-hover"></table>')
                                    )
                                );

                                btn.prop('disabled', false);
                                showSuccess('Проект успешно добавлен!');
                            } else {
                                showError(data.error);
                                return false;
                            }
                        },
                        error: function () {
                            showError('Some uncaught error occurs during saving data');
                            return false;
                        }
                    });
                });
                $('.cancel_project_btn_action').on('click', function () {
                    $(this).closest('.panel').remove();
                    btn.prop('disabled', false);
                });

            });

            // Редактирование проекта
            main_obj.on('click', '.edit_project', function () {
                var project_id = $(this).data('project_id');
                var newRow = $(this).closest('div.row');
                var oldValue = newRow.find('.no-left-padding').html();
                var savedOldDOM = newRow.find('div.no-left-padding').remove();
                newRow.find('div.text-right').hide();

                newRow
                    .append($('<div class="col-lg-11 no-left-padding"></div>')
                        .append($('<div class="input-group"></div>')
                            .append($('<input>').attr({
                                type: 'text',
                                class: 'form-control',
                                placeholder: 'Start typing here to update project name'
                            }).val(oldValue))
                            .append($('<span class="input-group-btn"></span>')
                                .append($('<button class="btn btn-success edit_project_btn_action"></button>').attr('type', 'button')
                                    .append($('<span class="fa fa-check"></span>'))
                            )
                                .append($('<button class="btn btn-danger cancel_current_project_btn_action">').attr('type', 'button')
                                    .append($('<span class="fa fa-times"></span>'))
                            )
                        )
                    )
                );

                // Обработчик для редактирования проекта и отмены добавления
                newRow.on('click', '.edit_project_btn_action', function () {
                    var $_this = $(this);
                    var params, dataStr;
                    var input = $_this.closest('div').find('input');
                    params = {
                        'id': project_id,
                        'name': input.val().replace(/(<([^>]+)>)/ig, "").trim() // trim HTML tags
                    };
                    dataStr = $.param(params);

                    if (!params.name) {
                        showError('Название проекта не должно быть пустым!');
                        return false;
                    }

                    $.ajax({
                        url: 'api/action.php?action=update_project',
                        type: 'POST',
                        data: dataStr,
                        timeout: 30000,
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                var row = $_this.closest('div.row');
                                $_this.closest('div.no-left-padding').remove();
                                row.find('.text-center').after($('<div class="col-lg-9 no-left-padding"></div>').html(params.name));
                                row.find('div.text-right').show();

                                newRow.off('click');
                            } else {
                                showError(data.error);
                                return false;
                            }
                            showSuccess('Проект "' + params.name + '" успешно отредактирован!');
                        },
                        error: function () {
                            showError('Some uncaught error occurs during saving data');
                            return false;
                        }
                    });
                });
                newRow.on('click', '.cancel_current_project_btn_action', function () {
                    newRow.find('div.no-left-padding').remove();
                    newRow.find('.text-center').after(savedOldDOM);
                    newRow.find('div.text-right').show();

                    newRow.off('click');
                });


            });

            // Удаление проекта
            main_obj.on('click', '.delete_project', function () {
                var $_this = $(this);
                var params, dataStr;
                params = {
                    'id': $_this.data('project_id')
                };
                dataStr = $.param(params);

                if (!confirm("Вы действительно хотите удалить проект и все задачи?"))
                    return false;

                $.ajax({
                    url: 'api/action.php?action=del_project',
                    type: 'POST',
                    data: dataStr,
                    timeout: 30000,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            $_this.closest('.panel').remove();
                        } else {
                            showError(data.error);
                            return false;
                        }
                        showSuccess('Проект успешно удален!');
                    },
                    error: function () {
                        showError('Some uncaught error occurs during saving data');
                        return false;
                    }
                });
            });

            // Добавление таска
            main_obj.on('click', '.add_task', function () {
                var $_this = $(this);
                var params, dataStr;
                var input = $_this.closest('.add_project').find('input');
                var tableContent = $_this.closest('.panel').find('div.panel-body table');
                params = {
                    'project_id': $_this.data('project_id'),
                    'descr': input.val().replace(/(<([^>]+)>)/ig, "").trim() // trim HTML tags
                };
                dataStr = $.param(params);

                if (!params.descr) {
                    showError('Задача не должна быть пустая!');
                    return false;
                }
                $.ajax({
                    url: 'api/action.php?action=save_task',
                    type: 'POST',
                    data: dataStr,
                    timeout: 30000,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            /* Скругляем края и очищаем инпут */
                            $_this.closest('.panel').css('border-radius', '');
                            input.val('');

                            tableContent
                                .append($('<tr data-priority="priority_' + data.task_id + '"></tr>')
                                    .append($('<td class="task-status"></td>')
                                        .append($('<i class="fa fa-square-o style-cursor change_task_status"></i>').data('task_id', data.task_id)))
                                    .append($('<td class="task-description"></td>').html(params.descr))
                                    .append($('<td class="controls"></td>')
                                        .append($('<div class="main_controls"></div>')
                                            .append($('<i class="fa fa-arrows arrows-task style-cursor"></i>').data('task_id', data.task_id))
                                            .append($('<span class="separator"></span>'))
                                            .append($('<i class="fa fa-pencil edit_task style-cursor"></i>').data('task_id', data.task_id))
                                            .append($('<span class="separator"></span>'))
                                            .append($('<i class="fa fa-trash delete_task style-cursor"></i>').data('task_id', data.task_id)
                                        )
                                    )
                                        .append($('<div class="edit_controls"></div>')
                                            .append($('<i class="fa fa-check edit_task_btn_action style-cursor"></i>'))
                                            .append($('<span class="separator"></span>'))
                                            .append($('<i class="fa fa-times cancel_task_btn_action style-cursor"></i>')
                                        )
                                    )
                                )
                            );
                        } else {
                            showError(data.error);
                            return false;
                        }
                        showSuccess('Задача успешно добавлена!');

                        // добавляем возможность менять приоритет задачи
                        if ($_this.closest('.panel').find('table tbody tr').length === 1)
                            makeSortable($_this.closest('.panel').find('table tbody'));
                    },
                    error: function () {
                        showError('Some uncaught error occurs during saving data');
                        return false;
                    }
                });
            });

            // Редактирование таска
            main_obj.on('click', '.edit_task', function () {
                var task_id = $(this).data('task_id');
                var base = $(this).closest('tr');
                var oldValue = base.find('.task-description').text();

                base.find('.task-description').html('')
                    .append($('<input>').attr({
                        'class': 'form-control',
                        'type': 'text',
                        'placeholder': 'Start typing here to update a task'
                    }).val(oldValue)
                );
                base.find('div.main_controls').hide();
                base.find('div.edit_controls').show();

                base.on('click', '.edit_task_btn_action', function () {
                    var $_this = $(this);
                    var params, dataStr;
                    var input = $_this.closest('tr').find('input');
                    params = {
                        'task_id': task_id,
                        'descr': input.val().replace(/(<([^>]+)>)/ig, "").trim() // trim HTML tags
                    };
                    dataStr = $.param(params);

                    if (!params.descr) {
                        showError('Задача не должна быть пустая!');
                        return false;
                    }

                    $.ajax({
                        url: 'api/action.php?action=update_task',
                        type: 'POST',
                        data: dataStr,
                        timeout: 30000,
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                var row = $_this.closest('tr');
                                row.find('.task-description').html(params.descr);
                                row.find('div.main_controls').show();
                                row.find('div.edit_controls').hide();

                                base.off('click');
                            } else {
                                showError(data.error);
                                return false;
                            }
                            showSuccess('Задача успешно отредактирована!');
                        },
                        error: function () {
                            showError('Some uncaught error occurs during saving data');
                            return false;
                        }
                    });
                });
                base.on('click', '.cancel_task_btn_action', function () {
                    base.find('.task-description').html(oldValue);
                    base.find('div.main_controls').show();
                    base.find('div.edit_controls').hide();

                    base.off('click');
                });
            });

            // Удаление таска
            main_obj.on('click', '.delete_task', function () {
                var $_this = $(this);
                var panel = $_this.closest('div.panel');
                var params, dataStr;
                params = {
                    'task_id': $_this.data('task_id')
                };
                dataStr = $.param(params);

                if (!confirm("Вы действительно хотите удалить задачу?"))
                    return false;

                $.ajax({
                    url: 'api/action.php?action=del_task',
                    type: 'POST',
                    data: dataStr,
                    timeout: 30000,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            $_this.closest('tr').remove();
                            if (!panel.find('tr').length)
                                panel.css('border-radius', 0);
                        } else {
                            showError(data.error);
                            return false;
                        }
                        showSuccess('Задача успешно удалена!');
                    },
                    error: function () {
                        showError('Some uncaught error occurs during saving data');
                        return false;
                    }
                });
            });

            // Смена статуса таска
            main_obj.on('click', '.change_task_status', function () {
                var $_this = $(this);
                var params, dataStr;
                params = {
                    'task_id': $_this.data('task_id')
                };
                dataStr = $.param(params);

                $.ajax({
                    url: 'api/action.php?action=change_status',
                    type: 'POST',
                    data: dataStr,
                    timeout: 30000,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            if ($_this.hasClass('fa-square-o')) {
                                $_this.removeClass('fa-square-o').addClass('fa-check-square-o text-success');
                            } else {
                                $_this.removeClass('fa-check-square-o text-success').addClass('fa-square-o');
                            }
                        } else {
                            showError(data.error);
                            return false;
                        }
                        showSuccess('Статус задачи успешно изменен!');
                    },
                    error: function () {
                        showError('Some uncaught error occurs during saving data');
                        return false;
                    }
                });
            });
        }

        function makeSortable(el) {
            el.sortable({
                placeholder: "ui-state-highlight",
                handle: ".arrows-task",
                cursor: 'move',
                update: function (event, ui) {
                    var data = $(this).sortable('serialize', {
                        attribute: 'data-priority'
                    });

                    $.ajax({
                        url: 'api/action.php?action=update_priority',
                        type: 'POST',
                        data: data,
                        timeout: 30000,
                        dataType: 'json',
                        success: function (data) {
                            if (!data.success) {
                                showError(data.error);
                                return false;
                            }
                            showSuccess('Приоритет задачи успешно изменен!');
                        },
                        error: function () {
                            showError('Some uncaught error occurs during saving data');
                            return false;
                        }
                    });
                }
            });
        }

        function createInfoBoxes() {
            // Block layer
            main_obj.append($('<div>').attr('id', 'blockLayer').css('display', 'none')
                    .append($('<h1>').html('<i class="fa fa-spinner fa-spin"></i> Загружаем...'))
            );
        }

        function showError(message) {
            $.growlUI('Ошибка', message, 'error');
        }

        function showSuccess(message) {
            $.growlUI('Отлично', message, 'success');
        }
    }
})(jQuery);