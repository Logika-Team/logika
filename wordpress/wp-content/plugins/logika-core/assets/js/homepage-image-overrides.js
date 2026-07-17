(function ($, acf, settings) {
	'use strict';

	function profile(field) {
		var profiles = settings.profiles[field.get('key')];
		var row = field.$el.closest('.acf-row').data('id');
		var match = typeof row === 'string' ? row.match(/^row-(\d+)$/) : null;
		var index = match ? Number(match[1]) : 0;

		return profiles ? (profiles[index] || profiles[0]) : null;
	}

	function rowIndex(field) {
		var row = field.$el.closest('.acf-row').data('id');
		var match = typeof row === 'string' ? row.match(/^row-(\d+)$/) : null;

		return match ? Number(match[1]) : 0;
	}

	function source(field) {
		var sources = settings.sources[field.get('key')];
		var index = rowIndex(field);
		var originalImage = original(field);

		return originalImage ? (originalImage.url || originalImage.source_url || '') : (sources ? (sources[index] || sources[0] || '') : '');
	}

	function original(field) {
		return settings.originals[field.get('key')] || null;
	}

	function error(profile) {
		return 'Оберіть зображення щонайменше ' + profile.width + ' × ' + profile.height + ' px з такими самими пропорціями.';
	}

	function isRelaxed(field) {
		return settings.relaxedFields.indexOf(field.get('key')) !== -1;
	}

	function isValid(attachment, profile, field) {
		var image = attachment.attributes || attachment;
		var type = image.mime || image.mime_type;
		var ratio = Number(image.width) / Number(image.height);
		var expected = profile.width / profile.height;

		return ['image/jpeg', 'image/png', 'image/webp'].indexOf(type) !== -1 && (isRelaxed(field) || Number(image.width) >= profile.width && Number(image.height) >= profile.height && Math.abs(ratio / expected - 1) <= 0.02);
	}

	function choose(field) {
		var fieldProfile = profile(field);
		acf.newMediaPopup({
			mode: 'select',
			title: 'Замінити зображення',
			field: field.get('key'),
			multiple: false,
			library: field.get('library'),
			allowedTypes: field.get('mime_types'),
			select: function (attachment) {
				if (fieldProfile && !isValid(attachment, fieldProfile, field)) {
					field.showNotice({ text: error(fieldProfile), type: 'error' });
					return;
				}

				setAttachment(field, attachment);
				setSelectedPreview(field, attachment);
				syncPreview(field);
			}
		});
	}

	function setAttachment(field, attachment) {
		var image = attachment && (attachment.attributes || attachment);

		acf.val(field.$input(), String(image && image.id || ''));
		field.render(attachment);
	}

	function setSelectedPreview(field, attachment) {
		var image = attachment.attributes || attachment;
		var url = image.url || image.source_url || '';

		if (url) {
			field.$el.find('.logika-image-override-selected img').attr('src', url);
		}
	}

	function syncPreview(field) {
		var originalImage = original(field);
		var hasValue = Boolean(field.val()) && (!originalImage || Number(field.val()) !== Number(originalImage.id));
		var selectedUrl = field.$el.find('.acf-image-uploader img').attr('src');

		if (selectedUrl) {
			field.$el.find('.logika-image-override-selected img').attr('src', selectedUrl);
		}

		field.$el.find('.logika-image-override-current').toggle(!hasValue);
		field.$el.find('.logika-image-override-selected').toggle(hasValue);
	}

	function hideLegacyFields($el) {
		Object.keys(settings.legacyFields).forEach(function (key) {
			($el || $(document)).find('.acf-field[data-key="' + settings.legacyFields[key] + '"]').hide();
		});
	}

	function enhance(field) {
		var fieldProfile = profile(field);
		var actions;
		var input;
		var panel;

		if (!field || !fieldProfile) {
			if (!field || (!original(field) && field.get('key') !== 'field_review_photo' && settings.managedFields.indexOf(field.get('key')) === -1)) {
				return;
			}
		}

		if (field.$el.data('logika-image-override')) {
			syncPreview(field);
			return;
		}

		field.$el.data('logika-image-override', true);
		field.$el
			.addClass('logika-image-override-field')
			.css({ width: '100%', maxWidth: 'none', flexBasis: '100%' });
		if (fieldProfile) {
			field.$el.children('.acf-label').hide();
		}
		input = field.$el.find('.acf-input').first().css({ width: '100%', maxWidth: 'none' });
		input.children('.acf-image-uploader').addClass('logika-image-override-native').hide();
		panel = $('<div class="logika-image-override-panel" style="width:100%;max-width:none"></div>').prependTo(input);
		if (source(field)) {
			$('<div class="logika-image-override-current"><p><strong>Оригінальне зображення</strong></p><img alt="Оригінальне зображення" style="max-width:300px;height:auto"></div>')
				.find('img').attr('src', source(field)).end()
				.appendTo(panel);
		}
		$('<div class="logika-image-override-selected"><p><strong>Обране зображення</strong></p><img alt="Обране зображення" style="max-width:300px;height:auto"></div>').appendTo(panel);
		actions = $('<p class="acf-actions logika-image-override-actions"></p>').appendTo(panel);
		$('<a class="button button-primary logika-image-override-replace" href="#">Замінити зображення</a>')
			.appendTo(actions)
			.on('click', function (event) {
				event.preventDefault();
				choose(field);
			});
		$('<a class="button logika-image-override-reset" href="#">Повернути стандартне</a>')
			.appendTo(actions)
			.on('click', function (event) {
				event.preventDefault();
				var originalImage = original(field);
				if (originalImage) {
					setAttachment(field, originalImage);
					setSelectedPreview(field, originalImage);
				} else {
					field.removeAttachment();
				}
				syncPreview(field);
			});
		if (fieldProfile && !isRelaxed(field)) {
			$('<p class="description logika-image-override-description"></p>')
				.text('PNG, WebP або JPEG; щонайменше ' + fieldProfile.width + ' × ' + fieldProfile.height + ' px із такими самими пропорціями.')
				.appendTo(panel);
		}
		syncPreview(field);
	}

	function enhanceFields($el) {
		hideLegacyFields($el);
		($el || $(document)).find('.acf-field-image').addBack('.acf-field-image').each(function () {
			enhance(acf.getField($(this)));
		});
	}

	acf.addAction('ready', enhanceFields);
	acf.addAction('append', enhanceFields);
}(jQuery, acf, logikaHomepageImageOverrides));
