FileRename = {
	tr : {},
	td : {},
	name : '',
	newName : '',
	addRenameObj : function(name) {
		var tr = $('tr').filterAttr('data-file', name);
		var td = tr.children('td.filename');
		FileRename.tr = tr;
		FileRename.td = td;
		FileRename.name = name;
		tr.data('renaming', true);
		var input = $('<input class="filename"></input>').val(name).css("width", td.width());
		var form = $('<form></form>').attr("id", "renameForm");
		form.append(input);
		td.append(form);
		td.children('a.name').hide();
		input.focus();
		FileRename.setHandler(input, form);
	},
	rename : function(event) {
		event.stopPropagation();
		event.preventDefault();

		var tr = FileRename.tr;
		var td = FileRename.td;
		var name = FileRename.name;
		var newName = FileRename.newName;
		var img = td.children("img.fileImg");
		var path = td.children('a.name').attr('href');

		FileRename.tr.attr('data-file', newName);
		FileRename.td.children('a.name').attr('href', path.replace(encodeURIComponent(name), encodeURIComponent(newName)));
		if (newName.indexOf('.') > 0)
			basename = newName.substr(0, newName.lastIndexOf('.'));
		else
			basename = newName;

		//td.children('a.name').append(img);
		//var span=$('<span class="nametext"></span>');
		var extention = td.find('.extention');
		td.find('.nametext').text(basename).append(extention);

		//td.children('a.name').append(span);
		if (newName.indexOf('.') > 0) {
			td.find('.extention').text(newName.substr(newName.lastIndexOf('.')));
			//span.append($('<span class="extention">'+newName.substr(newName.lastIndexOf('.'))+'</span>'));
		}
		td.children('a.name').show();
		td.children('#renameForm').remove();
		$.get(OC.filePath('files', 'ajax', 'rename.php'), {
			dir : $('#dir').val(),
			newname : newName,
			file : name
		}, function() {
			tr.data('renaming', false);
		});
		return false;
	},
	setHandler : function(input, form) {
		input.on('click', function(event) {
			event.stopPropagation();
			event.preventDefault();
		}).on('blur', function() {
			form.trigger('submit');
		}).on('keyup', function() {
			var val = $(this).val();
			if (val != val.trim())
				$(this).val($(this).val().trim());
		});
		form.on('submit', function(event) {
			var newName = input.val();
			//過瀘特殊符號並放回input中
			newName = PFunctions.forbiddenChar(newName);
			FileRename.newName = newName;
			FileRename.rename(event);
		});
	},
}