// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('VTramit', () => {
  before (() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
    })
  })

  // A partir d'aqui l'aplicació no para de fer requests, tot i que sembla que ve per defecte per nexcloud
  // ningun camp te id,
  it('Comprovem que les diferents seccions existeixin i tinguin tots els components necessaris.', () => {

    //--------------------------Dashboard-----------------------
    cy.get('.app-menu').should('exist')
    cy.get('.app-menu__list > :nth-child(5)').should('exist')
    cy.get('.app-menu__list > :nth-child(5)').click()

    //------------------------VTramit----------------------------
    cy.get('.board-actions').should('exist')
    cy.get('[data-test="new"]').should('exist')
    cy.get('[data-test="new"]').click()

    cy.get('select').should('exist')
    cy.get('select').select(0)
    //-----------------------Formulari--------------------------

    //----------------------- AMB IDENTIFICADORS--------------------
    cy.get('[data-test="cita-id"]').should('exist')
    cy.get('[data-test="department"]').should('exist')
    cy.get('[data-test="user"]').should('exist')
    cy.get('[data-test="channel"]').should('exist')
    cy.get('[data-test="topic"]').should('exist')
    cy.get('[data-test="date"]').should('exist')
    cy.get('[data-test="deadline"]').should('exist')
    cy.get('[data-test="comment"]').should('exist')

    cy.get('[data-test="isMain"]').should('exist')
    cy.get('[data-test="id"]').should('exist')
    cy.get('[data-test="name"]').should('exist')
    cy.get('[data-test="phone"]').should('exist')
    cy.get('[data-test="email"]').should('exist')
    cy.get('[data-test="add-stakeholder"]').should('exist')

    cy.get('[data-test="save"]').should('exist')

    /** 
    cy.get(':nth-child(4) > input').should('exist')
    cy.get(':nth-child(6) > select').should('exist')
    cy.get(':nth-child(8) > select').should('exist')
    cy.get(':nth-child(10) > select').should('exist')
    cy.get(':nth-child(12) > input').should('exist')
    cy.get(':nth-child(2) > .mx-datepicker > .mx-input-wrapper > .mx-input').should('exist')
    cy.get(':nth-child(16) > :nth-child(1) > .mx-datepicker > .mx-input-wrapper > .mx-input').should('exist')
    cy.get('textarea').should('exist')

    cy.get('.ml > :nth-child(1)').should('exist')
    cy.get('.ml > :nth-child(3) > input').should('exist')
    cy.get('.ml > :nth-child(5) > input').should('exist')
    cy.get('.ml > :nth-child(7) > input').should('exist')
    cy.get('.ml > :nth-child(9) > input').should('exist')
    cy.get('.justify-end > .primary').should('exist')
    cy.get('.fixed-bottom > .d-flex > .primary').should('exist')
    */
  })
})

