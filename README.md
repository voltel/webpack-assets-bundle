VoltelWebpackAssetsBundle
============================

**VoltelWebpackAssetsBundle** is a Symfony bundle that introduces several Twig functions 
that parse Webpack output and help to:
- get a **list of urls** for CSS/JS asset files for any **entrypoint(s)** of interest;
- get a **concatenated CSS content string** for **entrypoint(s)** of interest.

---
Note: the problems this bundle is trying to solve have been solved by 
a [Symfony/WebpackEncoreBundle](https://github.com/symfony/webpack-encore-bundle).
This is a light-weight solution for projects that 
rely on *traditional* webpack configuration (i.e. custom ``webpack.config.js``) 
and build process.   

Installation
============

Make sure Composer is installed globally, as explained in the
[Installation chapter](https://getcomposer.org/doc/00-intro.md) 
of the Composer documentation.

----------------------------------

Open a command console, enter your project directory and execute:
```shell script
$ composer require voltel/webpack-assets-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a terminal, enter your project directory and execute the
following command to download the latest stable version of this bundle:
```shell script
$ composer require voltel/extra-foundry-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the ``config/bundles.php`` file of your project:

```php
    // config/bundles.php
    return [
        // ...
        Voltel\WebpackAssetsBundle\VoltelWebpackAssetsBundle::class => ['all' => true],
    ];
```

Bundle configuration
=====================

Create configuration file (e.g. ``voltel_webpack_assets.yaml``) in ``config/packages/`` directory.

As usual, *default configuration* may be inspected by console command:
```shell script
$ php bin/console config:dump voltel_webpack_assets
```
To inspect *current configuration*, run console command:
```shell script
$ php bin/console debug:config voltel_webpack_assets
```

Default configuration:

```yaml
# in config/packages/voltel_webpack_assets.yaml

voltel_webpack_assets:

    # Name of the public web-content base-folder (e.g "public")
    public_dir_name:      public

    webpack:
        # Filepath of the "StatsWriterPlugin" plugin output RELATIVE to the project root
        stats_filepath:       stats.json

        # Name of the Webpack build output folder (e.g. "dist" or "build")
        output_dir_name:      dist

```

- **public_dir_name**: name of the directory with files accessible from the Internet. 
  Webpack will save its output in a sub-folder of this directory 
  (see **webpack.output_dir_name** configuration parameter below). 
  
  The value may be a path relative to the project root, 
  which is unusual but not impossible (e.g. ``./public``).   
  
- **webpack.stats_filepath**: path to the file with Webpack statistics output, relative to the root of the project. 
  With default values, the ``stats.json`` file is located in the root of the project.
  If, for example, your Webpack configuration places generated ``stats.json`` file 
  in e.g. ``public/dist`` directory, use ``public/dist/stats.json`` value instead. 
  
- **webpack.output_dir_name**: name of the directory inside the public directory 
  (see **public_dir_name** configuration parameter above) 
  where Webpack outputs files created during the build. 
  The value may be a path relative to the public directory, 
  which is unusual but not impossible (e.g. ``./build``).   



Usage
============

HTML pages: ``entry_css_urls`` and ``entry_js_urls`` Twig functions
---------------------------------------------------------------------

The **VoltelWebpackAssetsBundle** introduces Twig functions
``entry_css_urls`` and ``entry_js_urls`` 
to identify asset files for entrypoints from the argument.

Each of these functions takes **exactly one argument**: 
it can be either a string with an entrypoint name 
or an array of strings with names of entrypoints to load assets from: 
```twig
    {# in homepage.html.twig #}

    {% set css_urls = entry_css_urls('homepage') %}
    {% set css_urls = entry_css_urls(['homepage']) %}
    {% set css_urls = entry_css_urls(['common_layout', 'homepage']) %}
    
    {% for c_this_relative_url in css_urls %}
        <link rel="stylesheet" href="{{ asset(c_this_relative_url) }}">
    {% endfor %}

    {% for c_this_relative_url in entry_js_urls(['common_layout', 'homepage']) %}
        <script src="{{ absolute_url(c_this_relative_url) }}">
    {% endfor %}
``` 

Similar result may be achieved with custom Twig functions --
**print_css_link_tags** and **print_js_script_tags** --
that print all ``<link>`` and ``<script>`` html tags at once
for the entrypoint(s) from the first argument:

```twig
    {# in homepage.html.twig #}
    
    {% set l_print_absolute_url = true %}

    {# to print <link type="stylesheet"> html tags in one go #}
    {{ print_css_link_tags('homepage') }}
    {{ print_css_link_tags(['common_layout', 'homepage']) }}
    {{ print_css_link_tags(['common_layout', 'homepage'], l_print_absolute_url) }}

    {# to print <script src=""> html tags in one go #}
    {{ print_js_script_tags('homepage') }}
    {{ print_js_script_tags(['common_layout', 'homepage']) }}
    {{ print_js_script_tags(['common_layout', 'homepage'], l_print_absolute_url) }}

```  


HTML email content: ``entry_css_source`` Twig function   
---------------------------------------------------------------

```twig
    {% set c_css_rules = entry_css_source('common_email') %}
    {% apply inline_css(c_css_rules) %}
        {# email html content #}
    {% andapply %}
```



Discussion
===============

The problem this bundle is trying to solve is known:
during the Webpack build, asset CSS and JS files 
1) may be unpredictably conglomerated into chunks with unknown names, and 
2) hash strings may be attached to output filenames.

Loading proper assets for any particular entrypoint, thus, 
depends on analyzing the Webpack output. 

This bundle facilitates loading CSS and JS files into your html page 
by providing *urls* for ``<link href="{{ css_url }}">"`` and 
``<script src="{{ js_script_url }}">"`` attributes 
in Twig templates 
for all assets the entrypoint(s) in your web page might depend on.

This bundle harnesses the output generated by Webpack, 
i.e. the ``"entrypoints"`` key of the ``stats.json`` output file. 

The best way to obtain the output file is arguably 
to use [**StatsWriterPlugin**](https://github.com/FormidableLabs/webpack-stats-plugin/blob/main/test/scenarios/webpack5/webpack.config.js) 
Webpack plugin. 
For this, in the ``webpack.config.js``, activate the **StatsWriterPlugin**, e.g.:
```javascript
const path = require("path");
const { StatsWriterPlugin } = require("webpack-stats-plugin");

const webpackConfig = {
    entry: entries,
    output: {
        filename: "[name]." + "[hash:6]" + ".js",
        path: path.resolve(__dirname, 'public', 'dist'),
        publicPath: '/dist/'
    },

    // ... Other configuration

    plugins: [
        // ... some built-in Webpack plugin instances
    ]   
};

// Activation of "StatsWriterPlugin" plugin 
webpackConfig.plugins.push(new StatsWriterPlugin({

    // choose your preferred location for webpack statistics output file 
    filename: "../../stats.json",

    // use the same stats config as for webpack standard output 
    stats: {
       all: false,
       entrypoints: true 
    }
}));


module.exports = webpackConfig;
```

After that, whenever you run Webpack, 
it will re-create the ``stats.json`` file with current information: 
```shell script
$ webpack
$ npx webpack
$ npx webpack --watch
```
Read plugin documentation 
at [ FormidableLabs/webpack-stats-plugin GitHub repository](https://github.com/FormidableLabs/webpack-stats-plugin/blob/main/test/scenarios/webpack5/webpack.config.js) 
for details. 

If you don't want to use the **StatsWriterPlugin**, 
you can modify the ``webpackConfig.stats`` output configuration:
```javascript
webpackConfig.stats = {
    all: false, 
    entrypoints: true, 
    //hash: false,
    //version: false,
    //timings: false,
    //assets: false,
    //chunks: false,
    //maxModules: 0,
    //modules: false,
    //reasons: false,
    //children: false,
    //source: false,
    //errors: false,
    //errorDetails: false,
    //warnings: false,
    //publicPath: false,
    //builtAt: false
};
``` 

You can make a build and re-create ``stats.json`` file in one go with the following command:
```shell script
$ webpack --json > stats.json
``` 


Trick with Twig blocks   
--------------------------

To populate the ``<link>`` and ``<script>`` tags in an html page generated by Twig template, 
a certain trick can be used: 
set an arbitrary context variable with name(s) of entrypoints to load 
in the extending child template (``entrypoints`` in this example).
 
If the ``entrypoints`` variable is defined in the context and is not ``null``, 
entrypoint(s) from this variable will be used.
Otherwise, a fallback value of ``common_layout`` entrypoint will be used by default. 

To demonstrate this approach, here are excerpts from ``base.html.twig`` template
and a child template ``homepage.html.twig`` extending from it: 

```twig
    {# in base.html.twig #}

    {% block stylesheets %}
        {% for c_this_relative_url in entry_css_urls(entrypoints ?? 'common_layout') %}
            <link rel="stylesheet" href="{{ asset(c_this_relative_url) }}">
        {% endfor %}
    {% endblock %}


    {% block javascripts %}
        {% for c_this_relative_url in entry_js_urls(entrypoints ?? 'common_layout') %}
            <script src="{{ asset(c_this_relative_url) }}"></script>
        {% endfor %}
    {% endblock javascripts %}
```

Notes to the above code:
 
- If context variable "entrypoints" is set, entrypoints listed in it will be used. 
  Otherwise, a fallback value of "common-layout" will be used.  

- To prevent loading "common_layout.js", the "javascripts" block should be overridden on the page, not skipped.
 
With this set-up, in the extending child template 
it's enough to define a ``entrypoints`` variable in the template context:

```twig
    {# in homepage.html.twig #}

    {% set entrypoints = ['common_layout', 'homepage'] %}
    
    {% block title %}{{ 'homepage.title' | trans }}{% endblock %}

    {% block main %}{# page content #}{% endblock %}
    
```

With context variable ``entrypoints``, the parent ``base.html.twig`` template  
will create ``<link>`` and ``<script>`` tags automatically, 
for all stylesheet and javascript files imported in the provided entrypoints.

    Note, that since <link> tags will be created sequentially,
    in the order the entrypoints are listed, 
    stylesheets from the second entrypoint ("homepage" in this case) 
    will have precedence over CSS rules from stylesheets of "common_layout" entrypoint.
     
    Therefore, "common_layout" endpoint should go first 
    to let "homepage" override some CSS rules with page specific values, if needed. 


Hypothetically, if you want to load CSS files defined in both *"common_layout"* and *"homepage"* entrypoints 
but skip loading ```*.js``` files from *"commont_layout"* entrypoint, 
override the ``javascripts`` block, as in the example below:

```twig
    {# in homepage.html.twig #}

    {# This context variable will be automatically used by the parent's "stylesheets" block #}
    {% set entrypoints = ['common_layout', 'homepage'] %}

    {% block javascripts %}
        {# Parent's "javascript" block will load ONLY files from "homepage" entrypoint #}
        {% set entrypoints = 'homepage'  %}
        {{ parent() }}
        
    {% endblock %}
``` 

Inline css in Twig templates: ``entry_css_source`` function   
---------------------------------------------------------------

[``inline_css`` Twig filter](https://github.com/twigphp/cssinliner-extra)
helps embed css formatting into ``style`` attributes of html tags. 
---
Read more about how it is useful for html content of emails 
on [Symfony docs](https://symfony.com/doc/current/mailer.html#inlining-css-styles),
or watch a [screencast on Symfony Mailer](https://symfonycasts.com/screencast/mailer),
particularly, the following chapters:
- [09. Automatic CSS inlining](https://symfonycasts.com/screencast/mailer/inline-css),
- [10. Inlining CSS files](https://symfonycasts.com/screencast/mailer/css-files), and 
- [30. Styling Emails with Encore & Sass](https://symfonycasts.com/screencast/mailer/encore-css). 
---

Again, we must rely on ``stats.json`` file (specifically, its "entrypoints" key) 
created by ``webpack-stats-plugin`` or manually during the build.
 
This bundle introduces Twig function ``entry_css_source`` 
which will do the following:
- determine an array of ``*.css`` files to be loaded for the entrypoint(s) from the parameter;
- for each file, css rules will be read into a string;
- a concatenated string with CSS rules from all stylesheets 
  will be returned, to be consumed by e.g. ``inline_css`` filter. 

```twig inky
    {% apply inky_to_html | inline_css(entry_css_source('common_email')) %}
        <container>
            {# email html content #}
        </container>
    {% andapply %}
```

"common_email" entrypoint might import e.g. [Zurb Foundation (Inky)
css](https://zurb.com/university/responsive-emails-foundation) and custom css rules:
```scss
/* This uses Zurb Foundation (Inky) - css for emails */
@import "~foundation-emails/dist/foundation-emails.min.css";

.logo {
  text-align: center;
}
.email-header {
  color: darkslategrey;
  text-align: center;
}
.email-recipient {
  color: red;
}

```

Contributing
============
    Of course, open source is fueled by everyone's ability to give just a little bit
    of their time for the greater good. If you'd like to see a feature or add some of
    your *own* happy words, awesome! You can request it - but creating a pull request
    is an even better way to get things done.
    
    Either way, please feel comfortable submitting issues or pull requests: all contributions
    and questions are warmly appreciated :).
    
That being said, you may find it more suitable to 
use and contribute to [Symfony/WebpackEncoreBundle](https://github.com/symfony/webpack-encore-bundle) 
as a mainstream Symfony bundle that solves the same problems. 
