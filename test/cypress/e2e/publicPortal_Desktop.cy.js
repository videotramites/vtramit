// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('PublicPortal', () => {
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
    cy.get('[data-test="stakeholder"] a').invoke('attr','target','_self').click()
    
    // Mirem que el contingut estigui present
    cy.get('input[type="checkbox"]').should('exist').check()
    cy.get('.v-btn').should('exist').click()
    cy.get('.v-btn').should('exist').click()
    cy.get('[data-test="help"]').should('exist')
    cy.get('[data-test="rgdp"]').should('exist')
    cy.get('[data-test="videocall"]').should('exist').should('be.visible')

    cy.get('[data-test="upload"]').should('exist').click()
    cy.get('[data-test="fileUploads"]').should('exist').should('be.visible')

    cy.get('[data-test="download"]').should('exist').click()
    cy.get('[data-test="fileDownloads"]').should('exist').should('be.visible')

    cy.get('[data-test="sign"]').should('exist').click()
    cy.get('[data-test="signDocuments"]').should('exist').should('be.visible')

    cy.get('#jitsiConferenceFrame0').should('exist').should('be.visible')
  })
})

