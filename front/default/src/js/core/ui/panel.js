
import Panel from "coreui-panel/src/js/panel";


/**
 *
 */
class CoreUIPanel extends Panel {

    /**
     *
     */
    constructor() {

        super();

        this.setWrapperType('none');
        this.setContentFit('min');
        this.setTabType('underline');
    }

}

export default CoreUIPanel;