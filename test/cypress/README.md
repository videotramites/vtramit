# Cypress End2End Tests

## Introduction
This directory contains comprehensive end-to-end tests for the VTramit application user interface. The tests focus on reproducing the actions users perform when using the system. This testing approach will validate that the entire system components (UI, backend, database, APIs, external systems, etc) work together seamlessly.

## Prerequisites
- Nextcloud environment
- Docker for test
- NodeJs >= 18
- Cypress 14.5.x

## Test Organization Overview

All end‑to‑end (E2E) tests are stored under **`test/cypress/e2e`** and are written with Cypress.
Each test file models a **user story**: a complete, realistic workflow that a real user would execute in the application. The files are named after the functional area (or “module”) they cover, making it easy to locate the tests that correspond to a given part of the system.

| Module prefix       | Typical purpose                                           | Example file name                  |
| ------------------- | --------------------------------------------------------- | ---------------------------------- |
| **admin**           | Administrative UI (user management, settings, etc.)       | `admin‑manage‑users.cy.js`         |
| **attendantPortal** | Attendant‑portal specific operations                      | `attendantPortal‑check‑in.cy.js`   |
| **proceduresView**  | Viewing and interacting with procedures                   | `proceduresView‑filter‑list.cy.js` |
| **publicPortal**    | Public‑facing portal actions                              | `publicPortal‑register.cy.js`      |
| **vtramit**         | General Nextcloud‑wide actions (e.g., login)              | `vtramit‑login.cy.js`              |

### What each test file contains

* **A single, end‑to‑end flow** - from the initial entry point (e.g., landing page or login) through every step required to complete the story, ending with the expected final state.
* **Standard Cypress commands** - `cy.visit()`, `cy.get()`, `cy.click()`, `cy.type()`, assertions (`should`, `expect`), and any custom commands defined in the project’s support files.
* **Real‑world user interactions** - the actions performed reflect how a typical user would navigate the UI, ensuring the test validates the actual user experience rather than low‑level implementation details.

In summary, the `test/cypress/e2e` directory holds a collection of clearly named, self‑contained Cypress test files, each representing a complete user story for a specific module of the system. This organization provides a coherent, maintainable test suite that mirrors real user behaviour.

## Creating New Tests  

1. **Select the user story** you wish to automate.
2. **Choose the appropriate module prefix** (`admin`, `attendantPortal`, `proceduresView`, `publicPortal`, or `vtramit`) and give the spec file a descriptive name that reflects the actions it will perform.

User stories differ widely, so there is no rigid test‑file structure beyond Cypress’s own conventions. To avoid duplication we provide a set of custom Cypress commands—for example, a helper that creates a new appointment, which is used in many specs.

The test body itself consists of UI interactions (clicks, inputs, selections, etc.) combined with assertions on the application’s feedback, such as toast notifications, error messages, and success alerts.

In case we need to identify specific elements on the page the best practise is to add a "data-test" attribute to the element itself.


## Running Tests

Make sure that you have the latest application build:

```bash
make build
```

(the building step might imply having to configure and install the tools needed to compile the software)

To run all tests, type:

```bash
make run-cypress
```

You can view the results in the file `test/cypress/report/index.html`:


## Test Report

The report contains an entry for each e2e test performed with clear information to identify the module and "user story" being tested.
There are clear marks to identify if a test has passed (green check) or failed (red cross) and there is also a section at the top indicating the totals.
If a test fails the system also provides a screenshot marking the status of the user interface and that point and which element was causing the test failure.

## Best Practices

1. Stick to the test file naming conventions.
2. Test both valid and invalid scenarios.
3. Add "data-test" attributes the html elements used on the test cases.
4. Keep tests independent - don't rely on state from other tests.
5. Add thorough assertions with meaningful error messages.

## Additional Resources
- [Cypress Documentation](https://docs.cypress.io/app/get-started/why-cypress)
- [Nextcloud Testing Documentation](https://docs.nextcloud.com/server/latest/developer_manual/app_development/testing.html)
