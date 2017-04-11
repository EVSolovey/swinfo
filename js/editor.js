(function() {
    tinymce.create("tinymce.plugins.swi_button_plugin", {
        init : function(ed, url) {
            ed.addButton("swinfo", {
                title : "Add software shortcode",
                cmd : "swinfo_add",
                image : "https://cdn4.iconfinder.com/data/icons/linecon/512/add-32.png"
            });

            //button functionality.
            ed.addCommand("swinfo_add", function() {
                var selected_text = ed.selection.getContent();
                var return_text = "[swinfo id='' width='100%']";
                ed.execCommand("mceInsertContent", 0, return_text);
            });

        },

        createControl : function(n, cm) {
            return null;
        },

        getInfo : function() {
            return {
                longname : "Extra Buttons",
                author : "Evgenii Solovei",
                version : "1.0.1"
            };
        }
    });

    tinymce.PluginManager.add("swi_button_plugin", tinymce.plugins.swi_button_plugin);
})();