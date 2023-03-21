class AdminEvents {}

class Settings {
    toggleSelector(element) {
        let current = element.val(),
            selector = element.closest('.form-group').next();
        if (current === 'by_user_role') {
            selector.fadeIn('slow','linear');
        } else {
            selector.fadeOut('slow','linear');
        }
    }
}

if (!window.hm.admin) {
    class Admin {}
    window.hm.admin = new Admin();
}

window.hm.admin.events = new AdminEvents();
window.hm.admin.events.settings = new Settings();