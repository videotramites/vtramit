// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('Attendant Portal', () => {
  before(() => {
    cy.fixture('cypress.json').then((data) => {
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
      cy.get('.app-menu__list > :nth-child(5)').click()
      cy.url().should('include', '/vtramit')
    })
  })
  
  it("Comprovem que les diferents seccions existeixin i tinguin tots els components necessaris.", () => {
    // Creem una cita
    cy.crearCita()

    // Despleguem el menú de puntets
    cy.get('header.app-sidebar-header .action-item.app-sidebar-header__menu button.action-item__menutoggle').click()
    cy.get('[data-test="attendant"]').click()
    
    // La secció de signar fitxers existeix i s'hi pot accedir
    cy.get('[data-test="sign"]').should('exist').click()
    cy.get('#signatureOffset').should('exist').should('be.visible')


    // Puja el pdf en la signature i inenta firmar
    cy.get(':nth-child(3) > .files-panel--uploader > .files-panel--dropzone', { timeout: 10000 })
    .selectFile('test/cypress/fixtures/prova.pdf', { action: 'drag-drop' })
    cy.get('.toast-success').should('be.visible')
    cy.get('.signature-panel--top > :nth-child(1)').click()
    cy.get('.toast-error').should('not.be.visible')

  })
})
