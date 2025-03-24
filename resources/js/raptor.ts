import type { App } from 'vue'
import Raptor from './Raptor.vue';

import Table from './components/table';
import Form from './components/form';
import RaptorForm from './components/RaptorForm.vue';
import RaptorTable from './components/RaptorTable.vue';
import FlashMessageHandler from './components/FlashMessageHandler.vue';


interface PluginOptions {
  [key: string]: any
}

interface ComponentModule {
  default: any
}

const install = (app: App, options: PluginOptions = {}) => {
  const componentRegistry: string[] = [];
  app.component('Raptor', Raptor);
  app.component('v-raptor', Raptor);
  app.component('RaptorForm', RaptorForm);
  app.component('v-raptor-form', RaptorForm);
  app.component('RaptorTable', RaptorTable);
  app.component('v-raptor-table', RaptorTable);
  app.component('FlashMessageHandler', FlashMessageHandler);
  app.component('v-raptor-flash-message-handler', FlashMessageHandler);

  app.use(Table);
  app.use(Form);

}

export default {
  install
}