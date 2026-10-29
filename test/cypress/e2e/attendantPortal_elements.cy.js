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
    // Esperem i estem a editar cita

    // Despleguem el menú de puntets
    cy.get('header.app-sidebar-header .action-item.app-sidebar-header__menu button.action-item__menutoggle').click()
    cy.get('[data-test="attendant"]').click()
    
    // La secció de videotrucada existeix i té tots els components necessaris
    cy.get('[data-test="help"]').should('exist')
    cy.get('[data-test="rgdp"]').should('exist')
    cy.get('[data-test="videocall"]').should('exist').click()

    // La secció de pujada i baixada de fitxers existeix i té tots els components necessaris
    cy.get('[data-test="appointment"]').should('exist')
    cy.get('[data-test="uploads"]').should('exist').click()
    cy.get('[data-test="downloads"]').should('exist').click()
    cy.get('[data-test="sign"]').should('exist').click()
    cy.get('#jitsiConferenceFrame0').should('exist').should('be.visible')

    // La secció de pujada de fitxers existeix i s'hi pot accedir
    cy.get('[data-test="uploads"]').should('exist').click()
    cy.get('#uploadOffset').should('exist').should('be.visible')

    // La secció de baixada de fitxers existeix i s'hi pot accedir
    cy.get('[data-test="downloads"]').should('exist').click()
    cy.get('#downloadOffset').should('exist').should('be.visible')

    // La secció de signar fitxers existeix i s'hi pot accedir
    cy.get('[data-test="sign"]').should('exist').click()
    cy.get('#signatureOffset').should('exist').should('be.visible')
  })
})
