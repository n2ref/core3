import Control     from "../control";
import Core        from "../../../../core";
import CoreUITable from "../../table";


class ControlButtonDelete extends Control {

    _content = `<i class="bi bi-trash"></i> ${Core._('Удалить')}`;
    _onClick = null;
    _attr    = { class: "btn btn-warning" };
    _table   = null;


    /**
     * @param {string}      urlDelete
     * @param {CoreUITable} table
     * @param {string}      id
     */
    constructor(urlDelete, table, id) {

        super('button', id);

        this._table = table;

        this._onClick = function () {
            CoreUITable.deleteRecordsId(urlDelete, table.getSelectedRecordsId())
                .then(function () {
                    Core.app.reload();
                })
        }
    }


    /**
     * @param {string} content
     * @return {ControlButtonDelete}
     */
    setContent(content) {
        this._content = content;
        return this;
    }


    /**
     * @param {Object} attr
     * @return {ControlButtonDelete}
     */
    setAttr(attr) {

        this._attr = $.extend(true, this._attr || {}, attr);
        return this;
    }


    /**
     * @return {Object}
     */
    toObject() {

        if ( ! this._table.isAllow('delete')) {
            return null;
        }

        let result = super.toObject();

        if (this._content) { result.content = this._content }
        if (this._onClick) { result.onClick = this._onClick }
        if (this._attr)    { result.attr    = this._attr; }

        return result;
    }
}

export default ControlButtonDelete;