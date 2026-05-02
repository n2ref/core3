import Control from "../control";
import Core    from "../../../../core";


class ControlButtonAdd extends Control {

    _content = `<i class="bi bi-plus"></i> ${Core._('Добавить')}`;
    _url     = null;
    _table   = null;
    _attr    = { class: "btn btn-success" };

    /**
     * @param {string}      url
     * @param {CoreUITable} table
     * @param {string}      id
     */
    constructor(url, table, id) {

        super('link', id);

        this._table = table;

        if (url) {
            this.setUrl(url);
        }
    }


    /**
     * @param {string} url
     * @return {ControlButtonAdd}
     */
    setUrl(url) {
        this._url = url;
        return this;
    }


    /**
     * @param {string} content
     * @return {ControlButtonAdd}
     */
    setContent(content) {
        this._content = content;
        return this;
    }


    /**
     * @param {Object} attr
     * @return {ControlButtonAdd}
     */
    setAttr(attr) {

        this._attr = $.extend(true, this._attr, attr);
        return this;
    }


    /**
     * @return {Object}
     */
    toObject() {

        if ( ! this._table.isAllow('edit')) {
            return null;
        }

        let result = super.toObject();

        if (this._content) { result.content = this._content }
        if (this._url)     { result.url     = this._url }
        if (this._attr)    { result.attr    = this._attr }

        return result;
    }
}

export default ControlButtonAdd;