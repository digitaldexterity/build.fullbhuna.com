(function() {
	tinymce.create('tinymce.plugins.AdvancedImagePlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceAdvImage', function() {
				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class', '').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : url + '/image.htm',
					width : 480 + parseInt(ed.getLang('advimage.delta_width', 0)),
					height : 385 + parseInt(ed.getLang('advimage.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('image', {
				title : 'Insert/Update Image',
				text: 'Insert Image',
				cmd : 'mceAdvImage'
			});
		},

		getInfo : function() {
			return {
				longname : 'Insert/Update Image',
				author : 'Digital Dexterity',
				authorurl : 'https://www.digitaldexterity.co.uk',
				infourl : 'http://www.fullbhuna.com',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('advimage', tinymce.plugins.AdvancedImagePlugin);
})();