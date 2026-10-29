// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
      require('cypress-mochawesome-reporter/plugin')(on);
      require('@cypress/code-coverage/task')(on, config);

      return config;
    },

    reporter: 'cypress-mochawesome-reporter',
    reporterOptions: {
      reportDir: 'test/cypress/report',
      charts: true,
      embeddedScreenshots: true,
      inlineAssets: false
    },

    specPattern: 'test/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'test/cypress/support/e2e.js',
    fixturesFolder: 'test/cypress/fixtures',
    downloadsFolder: 'test/cypress/downloads',
    screenshotsFolder: 'test/cypress/screenshots',
  },
});
