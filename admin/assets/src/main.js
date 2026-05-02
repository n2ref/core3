
import Admin from "./js/admin";

import Index   from "./js/index/index";
import Logs    from "./js/logs";
import Modules from "./js/modules";
import Roles   from "./js/roles";
import Users   from "./js/users/users";

import langEn from "./js/lang/en";
import langRu from "./js/lang/ru";


Admin.index   = Index;
Admin.logs    = Logs;
Admin.modules = Modules;
Admin.roles   = Roles;
Admin.users   = Users;

Admin.lang.en = langEn;
Admin.lang.en = langRu;


export default Admin;
