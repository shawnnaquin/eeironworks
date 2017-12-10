=== HTML Forms ===
Contributors: Ibericode, DvanKooten, hchouhan, lapzor
Donate link: https://htmlforms.io/#utm_source=wp-plugin-repo&utm_medium=html-forms&utm_campaign=donate-link
Tags: form, contact form, email, contact, contact form 7
Requires at least: 4.5
Tested up to: 4.9
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.3

Not just another contact form plugin.

== Description ==

With [HTML Forms](https://htmlforms.io/#utm_source=wp-plugin-repo&utm_medium=html-forms&utm_campaign=plugin-description), you can easily add multi-purpose forms to your WordPress site.

The idea behind HTML Forms is different than most other form builder plugins. **You manage the form HTML. The plugin manages the PHP and JavaScript**.

That's right. No intuitive drag & drop interface but full control over the form HTML. No limitations.

We believe that dynamic form builders can be great, but they also severely limit your options. Also, they're slow.

HTML Forms aims to be simpler, faster and smarter.

You define your form fields in HTML and the plugin takes care of submitting the form, saving the form submission and running a configurable set of form actions.

> ### 1. Define your form fields
> You can use anything that resembles valid HTML. Ensure all `input` elements in your form have a `name` attribute to save data entered in that field.
> ### 2. Configure your form actions
> By default, form submissions are automatically saved for you. You can configure several other actions to run when a form is submitted, like sending an email notification or subscribing to MailChimp.
> ### 3. Show the form somewhere on your site
> You can show your form by using the `[hf_form]` shortcode in your WordPress posts, pages or widget areas.

## HTML Forms features

- Full control over the form HTML. If you want, we'll help you in generating the field HTML though.
- Each form submission is automatically saved in your database and can be viewed in a simple table format.
- Configure an unlimited amount of actions to run when a form is successfully submitted. For example, sending out email notifications.
- Access form field values by referencing the field name, eg `[NAME]` or `[EMAIL]`.
- Hide form or redirect to URL after a successful submission.
- Configurable & translatable form messages.
- Field validation.
- Developer friendly. HTML Forms comes with a myriad of JavaScript events and WordPress hooks that allow you to modify or extend the plugin's default behavior.

## Who is this for?

HTML Forms is for everyone that wants a flexible & high performing form solution.

- Users. Managing your own site? With HTML Forms, you don't need any technical knowledge to setup a contact form.
- Developers. Building a site for your client? You supply the form HTML, HTML Forms takes care of validating and processing the form.
- Theme developers. Shipping a theme? HTML Forms allows for complete blend-in with your theme, and you don't have to write yet another contact form plugin.

With HTML Forms you can create any type of form. Contact forms, registration forms, price quote forms, you name it. It's somewhat similar to Contact Form 7, but without yet another template language for you to learn.

For more information, please refer to [htmlforms.io](https://htmlforms.io/#utm_source=wp-plugin-repo&utm_medium=html-forms&utm_campaign=plugin-description).

#### Support

Use the [WordPress.org plugin forums](https://wordpress.org/support/plugin/html-forms) for community support where we try to help all of our users. If you found a bug, please create an issue on Github where we can act upon them more efficiently.

The [HTML Forms knowledge base](https://kb.htmlforms.io/#utm_source=wp-plugin-repo&utm_medium=html-forms&utm_campaign=plugin-description) covers a wide range of frequently asked questions and is updated on a regular basis.

#### Development

HTML Forms is being developed on GitHub. If you want to collaborate, please look at [ibericode/html-forms](https://github.com/ibericode/html-forms).


== Installation ==

1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **HTML Forms** and click "*Install now*"
1. Alternatively, download the plugin and upload the contents of `html-forms.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin

For more information, please refer to the [HTML Forms Knowledge Base](https://kb.htmlforms.io/#utm_source=wp-plugin-repo&utm_medium=html-forms&utm_campaign=installation-instructions).

== Frequently Asked Questions ==

#### Where can I find help?
Start by going through the [HTML Forms knowledge base](https://kb.htmlforms.io/#utm_source=wp-plugin-repo&utm_medium=html-forms&utm_campaign=plugin-faq) where we cover a wide range of frequently asked questions.

#### How to display a form in posts or pages?
Use the `[hf_form]` shortcode.

#### How to display a form in widget areas like the sidebar or footer?
Go to **Appearance > Widgets**, add the "Text Widget" to any of your widget areas and use the `[hf_form]` shortcode.

#### How do I show a form in a pop-up?

We recommend the [Boxzilla pop-up plugin](https://wordpress.org/plugins/boxzilla/) for this. You can use the `[hf_form]` shortcode in your pop-up box to render any of your forms.

#### Can I send an email when the form is submitted?

Yes! You can configure this by opening up the "Actions" tab when editing your form and clicking the "Email" button under "Available actions".


== Screenshots ==

1. Overview of forms in HTML Forms.
2. Editing form fields.
3. Sending an email when a form is submitted.
4. Viewing saved form submissions.
5. Hide form or redirect to URL after form submission.
6. Configurable form messages.

== Changelog ==


#### 1.0.5 - November 18, 2017

**Fixes**

- Could not save more than one "Email" action.


#### 1.0.4 - November 10, 2017

**Fixes**

- Incompatibility with PHP versions before 5.6.
- Data variables could not be placed on the same line.

**Improvements**

- Clear output buffer before sending AJAX response to prevent issues with response parsing.

**Additions**

- Added `hf_form_message_{$code}` filter hook.



#### 1.0.3 - November 6, 2017

**Additions**

- Added [support for conditional elements](https://kb.htmlforms.io/conditional-elements/) by using `data-show-if` and `data-hide-if` attributes.

**Improvements**

- Accept `id` argument in `[hf_form]` shortcode.
- Catch errors in shortcode's `slug` attribute.
- Allow changing form slug after initial form is saved.

**Fixes**

- Fixes stylesheet URL when option to load stylesheet is toggled.



#### 1.0.2 - October 30, 2017

**Fixes**

- Form validation always failing when form has 0 required fields.

**Improvements**

- Fake success response when honeypot validation fails.
- Validate request by comparing size of POST array with number of form fields.
- Ensure submit button never has label element when using the field helper.
- Optimize URL generation of asset files on frontend.

**Additions**

- Added `hf_validate_form_request_size` filter hook.


#### 1.0.1 - October 28, 2017

**Improvements**
- Added SVG admin menu icon.
- Field names are now sanitized before they are saved in the database.
- Submit button was missing for default form fields.
- Unneeded `<form>` tags are now stripped from the form before saving.

**Additions**
- Added `data-title` and `data-slug` attributes to the `<form>` element on the frontend.


#### 1.0 - October 25, 2017

Introducing a first version of HTML Forms, a different approach to forms for WordPress sites.



== Upgrade Notice ==
