(function ($, acf, settings) {
	'use strict';

	var defaultsData = {};
	var defaultsRequested = false;

	function fetchDefaults() {
		if (defaultsRequested || !settings.postId) {
			return;
		}
		defaultsRequested = true;

		$.ajax({
			url: settings.restUrl,
			data: { post: settings.postId },
			beforeSend: function (xhr) {
				xhr.setRequestHeader('X-WP-Nonce', settings.nonce);
			}
		}).done(function (response) {
			defaultsData = response || {};
			refreshRenderedFields();
		});
	}

	function refreshRenderedFields() {
		$('.logika-image-override-field').each(function () {
			var field = acf.getField($(this));
			if (field) {
				refreshField(field);
			}
		});
	}

	function profile(field) {
		var profiles = settings.profiles[field.get('key')];
		var index = rowIndex(field);

		return profiles ? (profiles[index] || profiles[0]) : null;
	}

	function inRepeaterRow(field) {
		return field.$el.closest('.acf-row').length > 0;
	}

	function rowIndex(field) {
		var row = field.$el.closest('.acf-row').data('id');
		var match = typeof row === 'string' ? row.match(/^row-(\d+)$/) : null;

		return match ? Number(match[1]) : 0;
	}

	function storageKey(field) {
		var key = field.get('key');

		return inRepeaterRow(field) ? key + '#' + rowIndex(field) : key;
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

	// Homepage "_override" fields fall back to a sibling base field on the
	// front end, so their default is "no override selected", not a stored
	// attachment. Every other field's default is whatever was captured on
	// its first admin save (see ImageOverrides::captureDefaults in PHP).
	function resolveDefault(field) {
		var key = field.get('key');

		if (settings.legacyFields[key]) {
			return { kind: 'clear' };
		}
		if (key === settings.reviewField) {
			return settings.reviewOriginal ? { kind: 'attachment', attachment: settings.reviewOriginal } : null;
		}

		var stored = defaultsData[storageKey(field)];

		return stored ? { kind: 'attachment', attachment: stored } : null;
	}

	function defaultPreviewUrl(field) {
		var resolved = resolveDefault(field);
		if (resolved && resolved.kind === 'attachment') {
			var image = resolved.attachment.attributes || resolved.attachment;
			return image.url || image.source_url || '';
		}

		var sources = settings.sources[field.get('key')];
		if (sources) {
			var index = rowIndex(field);
			return sources[index] || sources[0] || '';
		}

		return '';
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
		var resolved = resolveDefault(field);
		var defaultId = resolved && resolved.kind === 'attachment' ? Number((resolved.attachment.attributes || resolved.attachment).id) : null;
		var hasValue = Boolean(field.val()) && (null === defaultId || Number(field.val()) !== defaultId);
		var selectedUrl = field.$el.find('.acf-image-uploader img').attr('src');

		if (selectedUrl) {
			field.$el.find('.logika-image-override-selected img').attr('src', selectedUrl);
		}

		field.$el.find('.logika-image-override-current').toggle(!hasValue);
		field.$el.find('.logika-image-override-selected').toggle(hasValue);
	}

	function refreshField(field) {
		var currentUrl = defaultPreviewUrl(field);
		var currentBlock = field.$el.find('.logika-image-override-current');

		currentBlock.toggle(Boolean(currentUrl));
		if (currentUrl) {
			currentBlock.find('img').attr('src', currentUrl);
		}

		field.$el.find('.logika-image-override-reset').toggleClass('disabled', !resolveDefault(field));
		syncPreview(field);
	}

	function hideLegacyFields($el) {
		Object.keys(settings.legacyFields).forEach(function (key) {
			($el || $(document)).find('.acf-field[data-key="' + settings.legacyFields[key] + '"]').hide();
		});
	}

	function enhance(field) {
		var fieldProfile;
		var actions;
		var input;
		var panel;
		var resetLink;

		if (!field || field.get('type') !== 'image') {
			return;
		}

		if (field.$el.data('logika-image-override')) {
			refreshField(field);
			return;
		}

		fieldProfile = profile(field);
		field.$el.data('logika-image-override', true);
		field.$el.addClass('logika-image-override-field');
		if (fieldProfile) {
			field.$el.children('.acf-label').hide();
		}
		input = field.$el.find('.acf-input').first();
		input.children('.acf-image-uploader').addClass('logika-image-override-native').hide();
		panel = $('<div class="logika-image-override-panel"></div>').prependTo(input);
		$('<div class="logika-image-override-current"><p><strong>Оригінальне зображення</strong></p><img alt="Оригінальне зображення"></div>').appendTo(panel);
		$('<div class="logika-image-override-selected"><p><strong>Обране зображення</strong></p><img alt="Обране зображення"></div>').appendTo(panel);
		actions = $('<p class="acf-actions logika-image-override-actions"></p>').appendTo(panel);
		$('<a class="button button-primary logika-image-override-replace" href="#">Замінити зображення</a>')
			.appendTo(actions)
			.on('click', function (event) {
				event.preventDefault();
				choose(field);
			});
		resetLink = $('<a class="button logika-image-override-reset" href="#">Повернути стандартне</a>')
			.appendTo(actions)
			.on('click', function (event) {
				var resolved;
				event.preventDefault();
				if (resetLink.hasClass('disabled')) {
					return;
				}
				resolved = resolveDefault(field);
				if (!resolved) {
					return;
				}
				if ('attachment' === resolved.kind) {
					setAttachment(field, resolved.attachment);
					setSelectedPreview(field, resolved.attachment);
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
		refreshField(field);
	}

	function enhanceFields($el) {
		hideLegacyFields($el);
		($el || $(document)).find('.acf-field-image').addBack('.acf-field-image').each(function () {
			enhance(acf.getField($(this)));
		});
	}

	acf.addAction('ready', function ($el) {
		fetchDefaults();
		enhanceFields($el);
	});
	acf.addAction('append', enhanceFields);
}(jQuery, acf, logikaImageOverrides));
