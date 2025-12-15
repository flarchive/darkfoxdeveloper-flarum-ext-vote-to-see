import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import saveSettings from 'flarum/admin/utils/saveSettings';
import Button from 'flarum/common/components/Button';
import withAttr from 'flarum/common/utils/withAttr';
import Stream from 'flarum/common/utils/Stream';

export default class SettingsPage extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);
    const settings = app.data.settings || {};
    this.value = Stream(settings['darkfoxdeveloper-vote-to-see.lock_tag_slug'] || '');
  }

  onsubmit(e) {
    e.preventDefault();
    saveSettings({ 'darkfoxdeveloper-vote-to-see.lock_tag_slug': this.value() }).then(() => window.location.reload());
  }

  content() {
    return (
      <div className="SettingsPage">
        <div className="container">
          <form onsubmit={this.onsubmit.bind(this)}>
            <fieldset>
              <legend>General Settings</legend>
              <label>Tag for Content Locked (Require Vote)</label>
              <input className="FormControl" value={this.value() || ''} placeholder="locked" oninput={withAttr('value', this.value)} />
              <Button type="submit" className="Button Button--primary">Save</Button>
            </fieldset>
          </form>
        </div>
      </div>
    );
  }
}
