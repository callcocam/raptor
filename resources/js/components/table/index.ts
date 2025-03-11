import type { App } from 'vue'
import SCTable from './SCTable.vue';  
import HeaderActions from './HeaderActions.vue';

interface PluginOptions {
  [key: string]: any
}
 
const install = (app: App, options: PluginOptions = {}) => { 

  app.component('SCTable', SCTable);
  app.component('sc-table', SCTable);

  app.component('HeaderActions', HeaderActions);
  app.component('header-actions', HeaderActions);

}

export default {
  install
}