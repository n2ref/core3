import Form                from "coreui-form/src/js/form";
import HelperControlSubmit from "coreui-form/src/js/helpers/controls/submit";
import HelperControlLink   from "coreui-form/src/js/helpers/controls/link";


/**
 * @property {HelperControlLink} control.buttonCancel
 */
class CoreUIForm extends Form {

    _module  = '';
    _section = '';


    /**
     * @param {string} module
     * @param {string} section
     * @param {string} formId
     */
    constructor(module, section, formId) {

        let id = formId
            ? `${module}_${section}_${formId}`
            : `${module}_${section}`;

        super({
            id: id,
            lang: 'ru',
            validate: true,
            labelWidth: 160,
            fieldWidth: 200
        });

        this._module  = module;
        this._section = section;


        this.setValidResponseHeaders({
            "Content-Type": [
                "application\/json",
                "application\/json; charset=utf-8"
            ]
        });
        this.setValidResponseType([ "json" ]);


        this.control.submit = function (content) {
            return new HelperControlSubmit(content ? content : Core._('Сохранить'));
        };

        this.control.buttonCancel = function (url, content) {
            let button = new HelperControlLink(content ? content : Core._('Отмена'), url);
            button.setAttr({'class' : 'btn btn-secondary'});
            return button;
        };

        CoreUI.form._instances[id] = this;
    }

}

export default CoreUIForm;