# Lucent—the minimal HUBzero template
This is the HUBzero template with the "minimum" required files, scripts, markup, and styling.

# Installation
Upload the template files into the Hub's `app/templates/` directory and run the migration:

        php muse migration --file=Migration20191016000001TplLucent.php -d=up -f
        
This should install the template and list it in the admin panel under `Extensions -> Template Manager`


# Updating Styles
Hub's CSS styles are generated from LESS source files and any changes to CSS should be done by updating the corresponding LESS file source. LESS files should be then compiled to produce the template's main CSS stylesheet.

Even though the styles are broken down into multiple logical pieces, there is only one LESS file pulling all pieces together: `lucent/less/main.less` -- it needs to be compiled into `lucent/less/main.css` that is used by the template.

## To compile the LESS file you will need:

- [Install the LESS compiler](http://lesscss.org/usage/#command-line-usage-installing)
- Run the compiler to build the CSS (assuming you are in the `lucent/less/` directory or adjust the file paths accordingly:

        lessc main.less main.css
