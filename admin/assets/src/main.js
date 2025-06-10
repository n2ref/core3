
import Admin from "./js/admin";

import Index   from "./js/index";
import Logs    from "./js/logs";
import Modules from "./js/modules";
import Roles   from "./js/roles";
import Users   from "./js/users";

import langEn from "./js/lang/en";
import langRu from "./js/lang/ru";


Admin.Index   = Index;
Admin.Logs    = Logs;
Admin.Modules = Modules;
Admin.Roles   = Roles;
Admin.Users   = Users;

Admin.lang.en = langEn;
Admin.lang.en = langRu;


export default Admin;
