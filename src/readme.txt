=== WordPress Project Management by UpStream ===
Contributors: upstreamplugin
Tags: project, manage, management, project management, project manager, wordpress project management, crm, client, client manager, tasks, issue tracker, bug tracker, task manager
Requires at least: 4.5
Tested up to: 4.8
Stable tag: 1.11.1
License: GPL-3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WordPress Project Management by UpStream that is powerful, extensible & easy to use. Manage projects, clients, milestones, tasks, files & more.

== Description ==

Project management with WordPress has never been easier. UpStream is a free project management plugin that allows you to easily manage any type of project, right from within your WordPress website. Your clients can track the progress of their project via the frontend project view.

View our [Premium Extensions](https://upstreamplugin.com/extensions/) here.

= Project Features =

* Milestones & Tasks (that can be linked)
* Bug/Issue Tracker
* Upload Files & Documents
* Project Discussion thread
* Automatic Progress Tracking
* Custom Fields
* Custom Statuses

= Client Features =

* Client contact details, address, logo
* Custom fields
* Client Users (employees)
* Client login page to view their projects

= General Features =

* Built in Roles - Project Manager & Project User
* Custom Capabilities & Permissions
* Awesome looking frontend
* Customizable frontend templates
* Label Projects, Clients, Milestones, Tasks, Files & Bugs anything you like
* Developer friendly and highly customizable
* Translation ready

= Premium Extensions =
Add even more awesome features through the use of our extensions.

- [Frontend Edit](https://upstreamplugin.com/extensions/frontend-edit)
- [Project Timeline](https://upstreamplugin.com/extensions/project-timeline)
- [Customizer](https://upstreamplugin.com/extensions/customizer)
- [Email Notifications](https://upstreamplugin.com/extensions/email-notifications)
- [Copy Project](https://upstreamplugin.com/extensions/copy-project)

= Milestones & Tasks =

Milestones & tasks help you to successfully plan, track and manage your project from start to finish. Assign tasks & milestones to users, add start & end dates, color-coded statuses, notes and progress of the tasks & milestones. You can even add your own custom fields.

= Bug Tracking & Issue Reporting =

Easily report bugs or issues as they arise and just like milestones & tasks, you can assign the bug to a user, add a status, severity of the bug, a description, due date & attach files to each individual bug.

= Project Discussion =

Avoid email trails and keep the entire discussion about your project right where it should be, within the project! Any user can add to the discussion and with the Front End Edit extension and you can also allow your clients to add to the discussion.

= Front End View =

Your clients can view the details and the progress of the project via the front end. Clients can never access the WordPress admin. Using a customized login system, you can determine which users of your client can have access to the project and also to which parts of the project they can view.

= Highly Customizable =

Well thought out settings and options, customizable templates, add your own CSS, create custom fields wherever you like, create your own statuses with whatever colors you choose plus lots more. You can even rename projects, milestones, tasks, bugs, files and clients. Prefer to rename ‘Bugs’ as ‘Issues’? Rather call a ‘Project’ a ‘Plan’ or call a ‘Client’ a ‘Customer’? Go for it!

== Installation ==

= Minimum Requirements =

* WordPress 4.0 or greater
* PHP version 5.4 or greater

= Setting Up =

1. Activate the plugin
2. Go to UpStream > General Settings and configure the options as required
3. Create a Client by going to Projects > New Client
4. Create a Project by going to Projects > New Project
5. For a Quick Start guide and more detailed instructions, please visit the [Documentation](https://upstreamplugin.com/documentation/) page.


== Frequently Asked Questions ==

= Where can I find UpStream documentation? =

For a Quick Start guide and more detailed instructions, please visit the official [Documentation](https://upstreamplugin.com/documentation/) page

= Where can I get support? =

You can ask for help in the [UpStream Plugin Forum](https://wordpress.org/support/plugin/upstream).

= Will UpStream work with my theme? =

Yes, UpStream works independent of any theme.

= Why doesn't the UpStream frontend look like my theme? =

UpStream does not use the existing styling of your theme. The features and the very specific nature of the plugin make it impossible to integrate into existing themes. The plugin is highly customizable though, so you can tweak it to look the way you want it to.


== Screenshots ==

1. Editing a Project
2. Frontend view
3. List of Tasks
4. Editing a Milestone
5. Project settings
6. All project activity is logged
7. Adding a Bug
8. Editing a Client
9. Close up of Project Timeline (premium extension)


== Changelog ==

The format is based on [Keep a Changelog](http://keepachangelog.com)
and this project adheres to [Semantic Versioning](http://semver.org).

= [1.11.1] - @todo =

Added:
* Added the new UpStream Copy Project extension

Changed:
* Minor text changes

= [1.11.0] - 2017-08-01 =

Changed:
* Client Users are now fully WordPress Users
* New layout for the Extensions page
* Small frontend clean up
* Clean up admin menu
* Changed redirect url after install
* Display Project Name and Logo options are now "checked" by default
* Removed "Visibility" field in the Publish box for Clients and Projects
* A lot code enhancements

Fixed:
* Task's title field is now required
* Make sure UpStream custom roles are removed on uninstall
* Enhanced support for internationalization
* Fix Milestone field being required for Tasks
* Fixed some typos

= [1.10.4] - 2017-07-20 =

Changed:
* Clearer Project timeframe date-strings

Fixed:
* Fixed bug that was causing items to lose their dates if edited on localized sites
* Empty columns on frontend tables now receive "none"
* Some code redundancies
* Some columns on frontend tables are no longer orderable

= [1.10.3] - 2017-07-12 =

Changed:
* Users are now capable of logging in via /projects page
* UpStream Users no longer can log in in a project using the client's password
* Metaboxes filters were moved from the top to the bottom of the box
* Appearance enhancements

Fixed:
* Fixed random logo appearing in /projects page
* Fixed bug giving some users a hard time logging in a project
* Fixed uncommon redirection bug after logging off on frontend
* Fixed bug causing some usernames to be blank in several places
* UpStream Users no longer can access projects in which they're not involved in
* Fixed some clients losing their password after saving the form

= [1.10.2] - 2017-07-02 =

Changed:
* Moved metaboxes filters to the bottom
* Client logo and Project name are now displayed by default on frontend login page (this can be changed on the options page)

Fixed:
* Internal code cleanup

= [1.10.1] - 2017-06-29 =

Added:
* UpStream now verifies if the environment where it is been installed on satisfies a set of minimum requirements
* Added two new options to UpStream's settings: Login Page Client Logo and Login Page Project Name

Changed:
* Project overview section is now hidden during adding new projects
* Code enhancements

Fixed:
* Fixed potential issues breaking some JS after the latest update
* Fixed password related functions errors on PHP versions prior to 5.5

= [1.10.0] - 2017-06-26 =

Added:
* Added filters on metaboxes on admin
* Added support to embeds on several TinyMCE instances
* Added support to the Email Notifications plugin

Changed:
* Code optimizations
* Readded Add Media button on several TinyMCE instances
* UpStream no longer use Bootstrap modals

Fixed:
* Fixed text overflowing from the Project Ativity section
* Fixed bug with some fields on frontend
* Fixed URLs references on frontend when WP was using non-default Permalink settings

= [1.9.1] - 2017-06-06 =

Changed:
* CMB2 Library was updated

Fixed:
* Fixed bug that was causing data loss on projects which was updated in any way by regular UpStream users

= [1.9.0] - 2017-06-06 =

Added:
* Added options to disable Milestones, Tasks, Bugs, Files and Discussions on all projects
* Added support for user avatars setted by [Custom User Profile Photo](https://wordpress.org/plugins/custom-user-profile-photo) plugin
* Added support for user avatars setted by [WP User Avatar](https://wordpress.org/plugins/wp-user-avatar) plugin

Changed:
* WYSIWYG editors are now teeny
* The whole login workflow was refactored due performance and security issues
* Make "Bugs/Tasks assigned to me" sections title more clearer
* Plugin's changelog now follows [Keep a Changelog](http://keepachangelog.com) pattern

Fixed:
* Make sure there's always a PHP session available for UpStream
* Fixed some users losing their sessions forcing them to log in every page they visit

Security:
* Clients project passwords are now hashed and handled properly

= [1.8.0] - 2017-05-15 =

Added:
* Milestones, Tasks, Bugs and Files can now be enabled/disabled for individual projects

Fixed:
* Fixed bug with menu Tasks and Bugs notification counter

= [1.7.0] - 2017-05-08 =

Added:
* Added "My Tasks" and "My Bugs" metaboxes in frontend so users might see exactly what was assigned to them
* Projects are now auto-saved after adding a new "Task", "Bug", "Discussion" or "File"
* UpStream now automatically uses users BuddyPress avatars if BuddyPress plugin is active in your WP instance

Changed:
* Dropped "Project Author" metabox
* Metaboxes now fills 100% width instead of being fixed

Fixed:
* Fixed items count bug in both "Tasks" and "Bugs" pages in /wp-admin
* Fixes bug with "Mine" filter in "Tasks" and  "Bugs" pages in /wp-admin
* A couple of other minor bugs were fixed overall
* Fixed non-numeric PHP warning

= [1.6.1] - 2017-05-02 =

Changed:
* Replaced Tasks Note textarea with a WYSIWYG editor

Fixed:
* Fixed UI bug in Project Description editor where all buttons position were messed up in Text Mode

= [1.6.0] - 2017-05-01 =

Added:
* Added a Description field to projects
* New Customizer add-on

Changed:
* Rename plugin title
* Update vendor libraries
* Code tested up to WordPress 4.7.4
* Replace some textarea fields with WYSIWYG editor instances in project form

Fixed:
* Fixed some frontend UI bugs
* Fixed bug that was preventing some special users from loggin in via frontend

= [1.5.4] - 2017-04-20 =

Fixed:
* Drop Style Setting page
* Fixed dates format in frontend
* Fixed incomplete projects metadata in frontend
* Fixed UI error in admin
* Fixed feedback messages for clients-related forms

= [1.5.3] - 2017-03-21 =

Changed:
* Update mobile styles on the frontend

= [1.5.2] - 2017-03-13 =

Changed:
* Update Translations

= [1.5.1] - 2017-02-22 =

Fixed:
* Errors when logged in as subscriber
* Deleting roles and capabilities on uninstall

= [1.5.0] - 2017-02-20 =

Added:
* Add new Style Settings page
* Add Messages column (showing the count) in projects list screen

Fixed:
* Issue with internationalized dates not being saved. Reverted to Y-m-d format

= [1.4.3] - 2017-02-17 =

Changed:
* UI improvements on frontend view
* UI improvements on project edit screen in admin

Fixed:
* Issue with counts of tasks if nobody assigned to task

= [1.4.2] - 2017-02-17 =

Fixed:
* Issue with Project Activity. Remove post_type check that is not required

= [1.4.1] - 2017-02-16 =

Added:
* Admin Edit Project UI. Add Task and Bug end date to title bar

= [1.4.0] - 2017-02-16 =

Added:
* Add Project Activity section
* Add upstream_user_item() function to get any user item

Changed:
* Admin Edit Project UI. Move progress bar and add statuses into title bar

Fixed:
* Bug with checking for client permissions

= [1.3.2] - 2017-02-14 =

Fixed:
* Issue with not loading activity class

= [1.3.1] - 2017-02-14 =

Fixed:
* Issue with wrong client logo displaying on All Projects page

= [1.3.0] - 2017-02-10 =

Added:
* Add option in settings to completely disable bugs
* Add help text to Client User email field
* Add link on frontend sidebar for files

Changed:
* Minor updates to styling on Client edit screen

Fixed:
* Add a check for multiple email addresses on client login

= [1.2.0] - 2017-02-10 =

Added:
* Redirect to settings page after activation
* Add guided tour for first Project

Changed:
* Update styling on settings pages
* Update styling on Project edit screen
* Make first Milestone always open when editing or adding project

Fixed:
* Add various extra code checks such as isset(), is_array() etc throughout plugin
* Email link on Client Users within project
* Issue with adding Discussions in admin area

= [1.1.1] - 2017-02-08 =

Added:
* Add banners on Extension settings page

Changed:
* Update CSS on Extension settings page

Fixed:
* Typo on Extension settings page

= [1.1.0] - 2017-02-07 =

Added:
* Included translations for en_AU
* Included translations for en_NZ

= [1.0.2] - 2017-02-07 =

Changed:
* Modify upstream_count_total() function to return 0 for the id if not found

Security:
* Add proper escaping on items within admin Tasks page

= [1.0.1] - 2017-02-03 =

Changed:
* Update links to documentation from within plugin page

Fixed:
* Undefined index within upstream_count_total() function

= [1.0.0] - 2017-01-20 =

* Initial release
