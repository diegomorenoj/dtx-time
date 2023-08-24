# [Vuetify Material Dashboard PRO](https://store.vuetifyjs.com/products/vuetify-material-dashboard-free)

![version](https://img.shields.io/badge/version-1.2.0-blue.svg)[![Chat](https://img.shields.io/badge/chat-on%20discord-7289da.svg)](https://discord.com/invite/s93b7Fv)

![Product Gif](https://cdn.shopify.com/s/files/1/2695/0984/products/screen1_c85e2d5f-c56e-42a2-8361-47d8cc853fce.png?v=1605990715)

**Vuetify Material Dashboard PRO** is a beautiful resource built over [Vuetify](https://vuetifyjs.com/en/), [Vuex](https://vuex.vuejs.org/installation.html) and [Vuejs](https://vuejs.org/). It will help you get started and quickly developing dashboards in no time. Using the Dashboard is pretty simple but requires basic knowledge of Javascript, [Vuejs](https://vuejs.org/v2/guide/) and [Vue-Router](https://router.vuejs.org/en/).

## Getting Started

- Install Nodejs from the official [Nodejs page](https://nodejs.org/en/)
- Install yarn from the official [Yarn installation page](https://classic.yarnpkg.com/en/docs/install/#windows-stable).
- Open your terminal
- Navigate to the project
- Run `yarn install`
- Run `yarn serve` to start a local development server
- A new tab will be opened in your browser

You can also run additional tasks such as

- `yarn run build` to build your app for production
- `yarn run lint` to run linting.

## Vuetify

Vuetify is an Open Source UI Library that is developed exactly according to Material Design spec. Every component is handcrafted to bring you the best possible UI tools to your next great app. The development doesn't stop at the core components outlined in Google's spec. Through the support of community members and sponsors, additional components will be designed and made available for everyone to enjoy.

## Vuex

Vuex is a state management pattern + library for Vue.js applications. It serves as a centralized store for all the components in an application, with rules ensuring that the state can only be mutated in a predictable fashion. It also integrates with Vue's official [devtools](https://github.com/vuejs/vue-devtools) extension to provide advanced features such as zero-config time-travel debugging and state snapshot export / import.

## Vue-cli

We used the latest 3.x [Vue CLI](https://github.com/vuejs/vue-cli) which aims to reduce project configuration
to as little as possible. Almost everything is inside `package.json` + some other related files such as
`.babel.config.js`, `.eslintrc.js` and `.postcssrc.js`.

Let us know what you think and what we can improve below. And good luck with development!

## Table of Contents

- [Demo](#demo)
- [Quick Start](#quick-start)
- [Documentation](#documentation)
- [File Structure](#file-structure)
- [Browser Support](#browser-support)
- [Resources](#resources)
- [Reporting Issues](#reporting-issues)
- [Technical Support or Questions](#technical-support-or-questions)
- [Licensing](#licensing)
- [Useful Links](#useful-links)

## Demo

- [Start page](https://vuetify-material-dashboard.vuetifyjs.com/)
- [Icons page](https://vuetify-material-dashboard.vuetifyjs.com/components/icons/)
- [Notifications page](https://vuetify-material-dashboard.vuetifyjs.com/components/notifications/)

[View More](https://vuetify-material-dashboard.vuetifyjs.com/)

## Quick start

Quick start options:

- Download from [Vuetify](https://store.vuetifyjs.com/products/vuetify-material-dashboard-pro)

## Documentation

The documentation for **Vuetify Material Dashboard PRO** is hosted [here](https://vuetifyjs.com/).

## File Structure

Within the download you'll find the following directories and files:

<details>

```txt
vuetify-material-dashboard/
┣ public/
┃ ┣ android-chrome-192x192.png
┃ ┣ android-chrome-512x512.png
┃ ┣ apple-touch-icon.png
┃ ┣ favicon-16x16.png
┃ ┣ favicon-32x32.png
┃ ┣ favicon.ico
┃ ┣ index.html
┃ ┣ robots.txt
┃ ┗ site.webmanifest
┣ src/
┃ ┣ assets/
┃ ┃ ┣ clint-mckoy.jpg
┃ ┃ ┣ lock.jpg
┃ ┃ ┣ login.jpg
┃ ┃ ┣ logo.png
┃ ┃ ┣ pricing.jpg
┃ ┃ ┣ register.jpg
┃ ┃ ┣ vmd.svg
┃ ┃ ┗ vuetify.svg
┃ ┣ components/
┃ ┃ ┣ app/
┃ ┃ ┃ ┣ BarItem.vue
┃ ┃ ┃ ┣ Btn.vue
┃ ┃ ┃ ┣ Card.vue
┃ ┃ ┃ ┗ Tabs.vue
┃ ┃ ┣ Links.vue
┃ ┃ ┣ MaterialAlert.vue
┃ ┃ ┣ MaterialCard.vue
┃ ┃ ┣ MaterialChartCard.vue
┃ ┃ ┣ MaterialDropdown.vue
┃ ┃ ┣ MaterialRevealCard.vue
┃ ┃ ┣ MaterialSnackbar.vue
┃ ┃ ┣ MaterialStatsCard.vue
┃ ┃ ┣ MaterialWizard.vue
┃ ┃ ┣ PlanCard.vue
┃ ┃ ┣ TestimonyCard.vue
┃ ┃ ┗ ViewIntro.vue
┃ ┣ i18n/
┃ ┃ ┣ messages/
┃ ┃ ┃ ┣ ar.json
┃ ┃ ┃ ┗ en.json
┃ ┃ ┗ index.js
┃ ┣ layouts/
┃ ┃ ┣ default/
┃ ┃ ┃ ┣ widgets/
┃ ┃ ┃ ┃ ┣ Account.vue
┃ ┃ ┃ ┃ ┣ AccountSettings.vue
┃ ┃ ┃ ┃ ┣ DrawerHeader.vue
┃ ┃ ┃ ┃ ┣ DrawerToggle.vue
┃ ┃ ┃ ┃ ┣ GoHome.vue
┃ ┃ ┃ ┃ ┣ Notifications.vue
┃ ┃ ┃ ┃ ┗ Search.vue
┃ ┃ ┃ ┣ AppBar.vue
┃ ┃ ┃ ┣ Drawer.vue
┃ ┃ ┃ ┣ Footer.vue
┃ ┃ ┃ ┣ Index.vue
┃ ┃ ┃ ┣ List.vue
┃ ┃ ┃ ┣ ListGroup.vue
┃ ┃ ┃ ┣ ListItem.vue
┃ ┃ ┃ ┣ Settings.vue
┃ ┃ ┃ ┗ View.vue
┃ ┃ ┗ page/
┃ ┃   ┣ AppBar.vue
┃ ┃   ┣ Footer.vue
┃ ┃   ┣ Index.vue
┃ ┃   ┗ View.vue
┃ ┣ plugins/
┃ ┃ ┣ app.js
┃ ┃ ┣ chartist.js
┃ ┃ ┣ index.js
┃ ┃ ┣ vee-validate.js
┃ ┃ ┣ vue-meta.js
┃ ┃ ┣ vue-world-map.js
┃ ┃ ┣ vuetify.js
┃ ┃ ┣ vuex-pathify.js
┃ ┃ ┣ webfontloader.js
┃ ┃ ┗ world-map-vue.js
┃ ┣ router/
┃ ┃ ┗ index.js
┃ ┣ store/
┃ ┃ ┣ modules/
┃ ┃ ┃ ┣ app.js
┃ ┃ ┃ ┣ index.js
┃ ┃ ┃ ┣ sales.js
┃ ┃ ┃ ┗ user.js
┃ ┃ ┗ index.js
┃ ┣ styles/
┃ ┃ ┣ overrides.sass
┃ ┃ ┗ variables.scss
┃ ┣ util/
┃ ┃ ┣ globals.js
┃ ┃ ┣ helpers.js
┃ ┃ ┗ routes.js
┃ ┣ views/
┃ ┃ ┣ Buttons.vue
┃ ┃ ┣ Calendar.vue
┃ ┃ ┣ Charts.vue
┃ ┃ ┣ Dashboard.vue
┃ ┃ ┣ DataTables.vue
┃ ┃ ┣ Error.vue
┃ ┃ ┣ ExtendedForms.vue
┃ ┃ ┣ ExtendedTables.vue
┃ ┃ ┣ FullscreenMap.vue
┃ ┃ ┣ GoogleMaps.vue
┃ ┃ ┣ Grid.vue
┃ ┃ ┣ Icons.vue
┃ ┃ ┣ Lock.vue
┃ ┃ ┣ Login.vue
┃ ┃ ┣ Notifications.vue
┃ ┃ ┣ Pricing.vue
┃ ┃ ┣ Register.vue
┃ ┃ ┣ RegularForms.vue
┃ ┃ ┣ RegularTables.vue
┃ ┃ ┣ Rtl.vue
┃ ┃ ┣ Tabs.vue
┃ ┃ ┣ Timeline.vue
┃ ┃ ┣ Typography.vue
┃ ┃ ┣ UserProfile.vue
┃ ┃ ┣ ValidationForms.vue
┃ ┃ ┣ Widgets.vue
┃ ┃ ┗ Wizard.vue
┃ ┣ App.vue
┃ ┗ main.js
┣ .browserslistrc
┣ .editorconfig
┣ .env.local
┣ .eslintrc.js
┣ .gitignore
┣ README.md
┣ babel.config.js
┣ package.json
┣ vue.config.js
┗ yarn.lock
```

</details>

## Browser Support

Vuetify Material Dashboard aims to support the last two versions of the following browsers:

## Resources

- [Live Preview](https://vuetify-material-dashboard.vuetifyjs.com/)
- Product Page: [Product](https://store.vuetifyjs.com/products/vuetify-material-dashboard-pro)
- Documentation is [Here](https://vuetifyjs.com/)
- License Agreement: [License](https://store.vuetifyjs.com/licenses)
- Contact: [Contact](https://store.vuetifyjs.com/contact-us)
- Issues: [Github Issues Page](https://github.com/vuetifyjs/premium-theme-issues)

## Reporting Issues

We use GitHub Issues as the official bug tracker for the **Vuetify Material Dashboard** theme. Here is some advice for our users that want to report an issue:

1. Providing us reproducible steps for the issue will shorten the time it takes for it to be fixed.
2. Some issues may be browser specific, so specifying in what browser you encountered the issue might help.

## Technical Support or Questions

If you have questions or need help integrating the product please reach out in [Discord](https://discord.com/invite/s93b7Fv) or file an issue [here](https://github.com/vuetifyjs/premium-theme-issues).

## Licensing

## Useful Links

- [Documentation](https://vuetifyjs.com/)
- [Vuetify Store](https://store.vuetifyjs.com/)
- [Free Vuetify Themes](https://store.vuetifyjs.com/collections/free-products)
- [Discord](https://discord.com/invite/s93b7Fv)
- [Twitter](https://twitter.com/vuetifyjs)
