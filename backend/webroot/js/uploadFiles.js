
"use strict";

function getFilenamesMethod() {
	return $('input[name="filenames-method"]:checked').val();
}

function filenamesRadioChanged() {
	const selectedRadio = getFilenamesMethod();
	if (selectedRadio == "filenames") {
		disableFilesInput(false);
		if (filesSelected()) {
			showSubmitBtn(false);
			newFilesSelected();
		}
		$('#file-prefix-fields').css('display', 'none');
	} else if (selectedRadio == "constants") {
		showSubmitBtn(false);
		if (checkConstantsValidity() && filesSelected()) {
			newFilesSelected();
		}
		$('#file-prefix-fields').css('display', 'block');
	} else {
		console.error("Unhandled radio button");
	}
	clearFileStatusesTable();

}

function clearFilesInput() {
	$('input[name="files[]"]').val('');
}

function clearFileStatusesTable() {
	$('#file-statuses-table tbody').html('');
}

function overwriteCheckboxChanged(event) {
	const checkbox = event.target;
	const rowIdx = checkbox.id.substring(checkbox.id.indexOf('_') + 1);
	let row = $(`#row_${rowIdx}`);
	let status = $(`#status_${rowIdx}`);
	if (checkbox.checked) {
		row.removeClass('hasError');
		row.addClass('toUpdate');
		status.html('Overwrite');
	} else {
		row.removeClass('toUpdate');
		row.addClass('hasError');
		status.html('Exists');
	}
}

function getFileLinks(filepaths) {
	let html = '';
	for (let i = 0; i < filepaths.length; ++i) {
		if (i > 0) {
			html += ', ';
		}
		html += `<a href="${filepaths[i]}" target="_blank" rel="noopener noreferrer">${filepaths[i]}</a>`;
	}
	if (filepaths.length <= 0) {
		html += 'n/a';
	}
	return html;
}

function createFileStatusesTable(files) {
	let html = `
		<tr>
			<th style="width: 100px">Status</th>`
	const originalFile = getFilenamesMethod() == 'constants' ? `
			<th style="width: 150px">Original Filename</th>` : '';
	html += `
			${originalFile}
			<th style="width: 150px">Upload Filename</th>
			<th style="width: 400px">Existing AWS URLs</th>
			<th style="width: 150px">Overwrite?</th>
		</tr>`;
	for (let i = 0; i < files.length; ++i) {
		let rowClass = '';
		let statusText = 'New';
		if (files[i].validationError) {
			rowClass = 'hasError';
			statusText = 'Invalid filename';
		} else if (files[i].exists) {
			rowClass = 'hasError';
			statusText = 'Exists';
		}
		html += `
			<tr id="row_${i}" ${rowClass ? `class="${rowClass}"` : ''}>
				<td id="status_${i}">${statusText}</td>`
		const originalFilename = getFilenamesMethod() == 'constants' ? `
				<td>${files[i].originalFilename}</td>` : '';
		const validationNote = files[i].validationError
			? `<br><small>${files[i].validationError}</small>`
			: '';
		html += `
				${originalFilename}
				<td>${files[i].uploadFilename}${validationNote}</td>
				<td>${getFileLinks(files[i].awsFilepaths)}</td>
				<td>${files[i].exists && !files[i].validationError ? `<input type="checkbox" id="checkbox_${i}" name="checkbox_${i}">` : ''}</td>
			</tr>`;
		$('#file-statuses-table tbody').html(html);
	}

	$('input[type="checkbox"]').change(overwriteCheckboxChanged);
	$('#file-statuses').show();
}

function showSubmitBtn(show) {
	const submitBtn = $('button[type="submit"]');
	submitBtn.removeClass(show ? 'hidden' : 'visible');
	submitBtn.addClass(show ? 'visible' : 'hidden');
}

function handleCheckFilesResult(data) {
	const hasInvalid = data.response.some(file => file.validationError);
	createFileStatusesTable(data.response);
	showSubmitBtn(!hasInvalid);
}

function handleCheckFilesError(error) {
	console.error(error);
}

function newFilesSelected() {
	showSubmitBtn(false);
	let formData = new FormData(document.getElementById('files-upload-form'));

	$.ajax({
		type: "POST",
		url: getAjaxUrl('admin/files/checkSelectedFiles'),
		data: formData,
		dataType: 'json',
		processData: false,
		contentType: false,
		cache: false,
		success: function(data) {
			if (!data.status) {
				console.error("Error: " + data.message);
				return;
			}
			handleCheckFilesResult(data);
		},
		error: function(error) {
			handleCheckFilesError(error);
		}
	});
}

function disableFilesInput(disable) {
	$('input[name="files[]"]').prop('disabled', disable);
}

function checkConstantsValidity() {
	const disable =
		$('#name').val().trim() == '' || $('#description').val().trim() == '';
	disableFilesInput(disable);
	return !disable;
}

function filesSelected() {
	return $('input[name="files[]"]').val() != '';
}

function handleConstantsBlur() {
	if (checkConstantsValidity() & filesSelected()) {
		newFilesSelected();
	}
}

// On document ready
$(function () {
	// Event listeners
	$('input[name="filenames-method"]').change(filenamesRadioChanged);
	$('input[name="files[]"]').change(newFilesSelected);
	$('#name').on('input', checkConstantsValidity);
	$('#description').on('input', checkConstantsValidity);
	$('#name').blur(handleConstantsBlur);
	$('#description').blur(handleConstantsBlur);
});
