/**
 * Add Tree to container
 * 
 * @param container
 * @param path
 * @return void
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
 * @return void
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
 * @return void
 */
function set_editor(data) {
	if (!window.hasOwnProperty('editor')) {
		editor = $.pandaEdit.factory();
	}
    editor.gotoLine(0);
    $.pandaEdit.reset();
    editor.getSession().setValue(data.file);
    editor.setReadOnly(data.read_only);
    if (data.read_only == true) {
    	$.pandaEdit.label('readonly');
    } else {
    	$.pandaEdit.reset();
    	$.pandaEdit.label('reset');
    	var save = function() {$.pandaEdit.save(data.info.dirname + '/' + data.info.basename, editor.getSession().getValue());};
        $('#editor').keybind('keyup', {
      	  'C-s': save
        });
        $('#editor').keybind('keyup', {
        	'C-S-s': save
        });
        $('span.editor_file_save').bind("click", save);
    }
}

/**
 * save
 * 
 * @param path
 *            path to save
 * @return void
 */
function save(path) {
	var data = _editor.getContent();
	jQuery('#save').html('saving...').css('color', 'red');
	_editor.setFocus(true);
	jQuery.post('ajax/save.php', {
		path : path,
		data : data
	}, function(data, status) {
		jQuery('#save').html('saved').css('color', 'green').fadeOut('slow')
				.fadeIn('slow', function() {
					jQuery('#save').html('save').css('color', 'blue')
				});
	}, 'html')
}

/**
 * put folder path to val.
 * 
 * @param path
 * @return
 */
function folder_select(path) {
	foldar_path = path
}