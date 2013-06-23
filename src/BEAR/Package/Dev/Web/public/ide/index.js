/**
 * Add Tree to container
 *
 * @param container
 * @param path
 * @param is_dir
 * @param label
 */
function addTree(container, path, is_dir, label) {
	if (is_dir) {
		script = 'jqueryFileTree/jqueryFileTree.php';
	} else {
		script = 'ajax/files.php';
	}
	$(container).fileTree( {
		root : path,
		script : script,
		folderEvent : 'click',
		expandSpeed : 75,
		collapseSpeed : 75,
		multiFolder : true
	}, function(path) {
		load(path);
	});
	$(container).append(label);
}

/**
 * Ajax load
 * 
 * @param path
 */
function load(path) {
	$('#path').html('<em>Loading...</em>');
	var url = 'load.php';
	jQuery.post('ajax/load.php', {
		path : path
	}, function(data, status) {
		set_editor(data);
		$('#file_info').html(data.info);
		$('#path').html(path + "&nbsp;" + data.file_info);
	}, 'json');
}

/**
 * Load editor
 * 
 * @param data
 */
function set_editor(data) {
	if (!window.hasOwnProperty('editor')) {
		editor = $.codeEdit.factory();
	}
    editor.gotoLine(0);
    $.codeEdit.reset();
    editor.getSession().setValue(data.file);
    editor.setReadOnly(data.read_only);
    if (data.read_only == true) {
    	$.codeEdit.label('readonly');
    } else {
    	$.codeEdit.reset();
    	$.codeEdit.label('reset');
    	var save = function() {
            var file = data.info.dirname + '/' + data.info.basename;
            $.codeEdit.save(
                file,
                editor.getSession().getValue(),
                'ajax/save.php'
            );
        };
        editor.commands.addCommand({
            name: 'Save',
            bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
            exec: save
        });
        $('#save_now').click(save);
    }
}

/**
 * put folder path to val.
 * 
 * @param path
 */
function folder_select(path) {
	foldar_path = path
}