<?php
/**
 * @global $view array
 */
return <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{$view['file_path']}</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
    <link rel="icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script>
    <script src="//d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js"></script>
    <script>
        $.codeEdit = {
            changed : false,
            factory : function() {
                var editor = ace.edit("editor");
                editor.setTheme("ace/theme/eclipse");
                editor.getSession().setMode("ace/mode/php");
                window.aceEditor = editor;
                editor.getSession().setTabSize(4);
                editor.getSession().setUseSoftTabs(true);
                editor.getSession().setUseWrapMode(true);
                editor.renderer.setHScrollBarAlwaysVisible(false);
                editor.getSession().on('change', $.codeEdit.change);
                editor.setHighlightActiveLine(true);
                return editor;
            },
            save : function(file_path, data, save_url) {
                if(typeof save_url === 'undefined') save_url = "{$view['save_url']}";
                if ($.codeEdit.changed == false) {
                    return;
                }
                $.codeEdit.changed = false;
                jQuery.post(save_url, {
                    file : file_path,
                    contents : data
                }, this.label('save'), 'html')
            },
            change : function() {
                if ($.codeEdit.changed == true) {
                    return;
                }
                $.codeEdit.label('changed');
                $.codeEdit.changed = true;
            },
            reset : function() {
                $.codeEdit.label('reset');
                $.codeEdit.changed = false;
            },
            label : function(mode) {
                var label = 'div#label.editor_label span.editor_file_save';
                if (mode == 'reset') {
                    // reset
                    jQuery(label).html('SAVE').css('background-color', 'gray');
                } else if (mode == 'changed') {
                    // change
                    jQuery(label).html('SAVE').css('background-color', 'green');
                } else if (mode == 'readonly') {
                    // change
                    jQuery(label).html('Read Only').css('background-color', 'gray');
                } else if (mode == 'save') {
                    jQuery(label).html('Saving...').css('background-color', 'red').fadeOut().fadeIn('slow', function() {
                        jQuery(label).html('SAVE').css('background-color', 'gray');
                    });
                }
            }
        }

    </script>
    <style>
        body {
            overflow: hidden;
            font: 100% "Trebuchet MS", sans-serif;
            background: #fff; ]
        font-size: 16px;
            font-family: monospace;
            height: 99%;
            margin: 0px;
            padding: 3px 20px 20px;
            color: black;
        }

        #editor {
            margin: 0;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .editor_label {
            padding: 4px;
            position: absolute;
            right: 16px;
            top: 2px;
            z-index: 10;
        }

        .editor_file {
            background-color: #FEF49C;
            color: black;
            font-family: arial, sans-serif;
            font-size: 12px;
            padding: 4px;
        }

        .error {
            background-color: red;
            color: white;
            font-family: arial, sans-serif;
            font-size: 12px;
            padding: 4px;
            left: 4px;
        }

        .editor_file_save {
            background-color: grey;
            color: white;
            font-family: arial, sans-serif;
            font-size: 12px;
            padding: 4px;
            cursor:	pointer;
            -moz-border-radius: 2px;
            -webkit-border-radius: 2px;
        }
    </style>
</head>
<body>

    <div id="label" class="editor_label">
        {$view['message']}
        <span class="editor_file">{$view['file_path']}</span>
        <span class="editor_file_save" id="save_now">Save</span>
    </div>
    <pre id="editor">{$view['file_contents']}</pre>
    <script>
    $(function(){
        editor = $.codeEdit.factory();
        editor.gotoLine({$view['line']});
        editor.setReadOnly({$view['is_read_only']});
        $.codeEdit.label('{$view['is_writable_label']}');
        var save = function() {\$.codeEdit.save("{$view['file_path']}", editor.getSession().getValue());};
        editor.commands.addCommand({
            name: 'Save',
            bindKey: {
                win: 'Ctrl-S',
                mac: 'Command-S'
            },
            exec: save
        });
         $('#save_now').click(save);
    });
    </script>
</body>
</html>
EOT;
