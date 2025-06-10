import Core from "./js/core";

import coreuiPanel      from 'coreui-panel/src/main';
import coreuiAlert      from 'coreui-alert/src/js/main';
import coreuiModal      from 'coreui-modal/src/main';
import coreuiInfo       from 'coreui-info/src/main';
import coreuiNotice     from 'coreui-notice/src/js/main';
import coreuiLayout     from 'coreui-layout/src/main';
import coreuiTable      from 'coreui-table/src/main';
import coreuiForm       from 'coreui-form/src/main';
import coreuiChart      from 'coreui-chart/src/main';
import coreuiBreadcrumb from 'coreui-breadcrumb/src/main';

import FieldSelect2 from 'coreui-form-field-select2/src/js/field';

import langEn from "./js/lang/en";

import jQuery    from "jquery";
import bootstrap from "bootstrap/dist/js/bootstrap.bundle.min";




Core.lang.en = langEn;

coreuiForm.fields.select2 = FieldSelect2;

let $      = jQuery;
let CoreUI = {
    'panel': coreuiPanel,
    'alert': coreuiAlert,
    'modal': coreuiModal,
    'info': coreuiInfo,
    'notice': coreuiNotice,
    'layout': coreuiLayout,
    'table': coreuiTable,
    'form': coreuiForm,
    'chart': coreuiChart,
    'breadcrumb': coreuiBreadcrumb,
};

// Присваиваем в window
Object.assign(window, {
    CoreUI,
    $,
    bootstrap
});

export default Core;