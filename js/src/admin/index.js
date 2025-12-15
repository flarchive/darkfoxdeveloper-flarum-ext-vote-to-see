import app from 'flarum/admin/app';
import SettingsPage from './components/SettingsPage';

app.initializers.add('darkfoxdeveloper-vote-to-see', (app) => {
  app.extensionData.for('darkfoxdeveloper-vote-to-see').registerPage(SettingsPage);
});
