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

  app.config.globalProperties.$raptor = {
    registerComponent(name: string, component: ComponentModule) {
      if (componentRegistry.includes(name)) {
        console.warn(`Component ${name} is already registered.`);
        return;
      }
      componentRegistry.push(name);
      app.component(name, component.default);
    }
    };
    app.config.globalProperties.$raptor.registerComponent('Raptor', Raptor);

}

export default {
  install
}