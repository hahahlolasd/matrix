function notify(type, iconClass, message, width = '300') {
    notif({
        type: type,
        msg: `<i class='${iconClass}'></i> ${message}!`,
        width: width
	});
}

function notifyUserDeleteSuccess() {
    notify('success', 'bi bi-trash', translations.user + " " + translations.delete_success + "!");
}

function notifyUserDeleteError() {
    notify('error', 'bi bi-trash', translations.user + " " + translations.delete_error + "!");
}

function notifyAjaxError() {
    notify('error', 'bi bi-bug', translations.ajax_error, '200');
}

function notifyUploadFields() {
    notify('success', 'bi bi-upload', translations.field_upload_success);
}

function notifyDatabaseError() {
    notify('error', 'bi bi-bug', translations.database_error, '200');
}

function notifyUploadError() {
    notify('error', 'bi bi-bug', translations.upload_error, '200');
}

function notifyXmlError() {
    notify('error', 'bi bi-bug', translations.xml_error, '200');
}

function notifyControllerDeleteSuccess() {
    notify('success', 'bi bi-trash', translations.controller + " " + translations.delete_success + "!");
}

function notifyControllerDeleteError() {
    notify('error', 'bi bi-trash', translations.controller + " " + translations.delete_error + "!");
}

function notifyFarmDeleteError() {
    notify('error', 'bi bi-trash', translations.farm + " " + translations.delete_error + "!");
}

function notifyFarmDeleteSuccess() {
    notify('success', 'bi bi-trash', translations.farm + " " + translations.delete_success + "!");
}

function notifyMachineDeleteError() {
    notify('error', 'bi bi-trash', translations.machine + " " + translations.delete_error + "!");
}

function notifyMachineDeleteSuccess() {
    notify('success', 'bi bi-trash', translations.machine + " " + translations.delete_success + "!");
}

function notifyFieldDeleteError() {
    notify('error', 'bi bi-trash', translations.field + " " + translations.delete_error + "!");
}

function notifyFieldDeleteSuccess() {
    notify('success', 'bi bi-trash', translations.field + " " + translations.delete_success + "!");
}

function notifyProfileUpdateSuccess() {
    notify('success', 'bi bi-person-check', translations.profile + " " + translations.update_success + "!");
}

function notifyProfileUpdateError() {
    notify('error', 'bi bi-bug', translations.profile + " " + translations.update_error + "!");
}

function notifyTifonDeleteSuccess() {
    notify('success', 'bi bi-trash', translations.tifon + " " + translations.delete_success + "!");
}
function notifyTifonDeleteError() {
    notify('error', 'bi bi-trash', translations.tifon + " " + translations.delete_error + "!");
}