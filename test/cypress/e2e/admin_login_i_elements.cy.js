// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('Admin', () => {
  before (() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.admin, data.password_admin)
      cy.url().should('include', '/dashboard')
    })
  })

  it('Comprovem que les diferents seccions existeixin i tinguin tots els components necessaris.', () => {
    // Anar a la part admin
    cy.get('.avatardiv > img').click()
    cy.get('#admin_settings').click()
    cy.get('[data-section-id="vtramit"]').click()

    // Comprovem els elements de les diferents parts
    cy.get('[data-test="generalConfig"]').should('exist')
    cy.get('[data-test="languages"]').should('exist')
    cy.get('[data-test="procedureList"]').should('exist')
    cy.get('[data-test="procedureFilter"]').should('exist')
    cy.get('[data-test="mailTemplates"]').should('exist')
    cy.get('[data-test="messageTemplates"]').should('exist')
    cy.get('[data-test="topics"]').should('exist')

    cy.get('[data-test="signature"]').click()
    cy.get('[data-test="signatures"]').should('exist')

    cy.get('[data-test="procedure"]').click()
    cy.get('[data-test="procedures"]').should('exist')

    cy.get('[data-test="public"]').click()
    cy.get('[data-test="publicConfig"]').should('exist')
    cy.get('[data-test="rgpd"]').should('exist')

    cy.get('[data-test="integrations"]').click()
    cy.get('[data-test="webhooks"]').should('exist')
  })
})

