
import Admin from "./js/admin";

import adminIndex   from "./js/admin.index";
import adminLogs    from "./js/admin.logs";
import adminModules from "./js/admin.modules";
import adminRoles   from "./js/admin.roles";
import adminUsers   from "./js/admin.users";

import langEn from "./js/lang/en";
import langRu from "./js/lang/ru";


Admin.index   = adminIndex;
Admin.logs    = adminLogs;
Admin.modules = adminModules;
Admin.roles   = adminRoles;
Admin.users   = adminUsers;

Admin.lang.en = langEn;
Admin.lang.en = langRu;


export default Admin;
