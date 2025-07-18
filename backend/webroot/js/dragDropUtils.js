
/**
 * This function is used to confirm the reset of a table element.
 * It displays a confirmation dialog to the user with a custom message.
 * If the user doesn't confirm, it resets the table element to its original state.
 * @param {*} tableEl - The table element to be updated
 * @param {*} tableStartClone - A clone of the original table element
 * @param {*} message - The message to be displayed in the confirmation dialog
 * @returns
 */
function confirmAndResetTable(tableEl, tableStartClone, message="Are you sure you want to update the table?") {
    if (!tableEl || !tableStartClone) {
        console.error("Invalid table element or clone provided.");
        return false;
    }

    if (!confirm(message)) {
        tableEl.innerHTML = tableStartClone.innerHTML;
        $(tableEl).tableDnDUpdate();
        return false;
    }
    return true;
}