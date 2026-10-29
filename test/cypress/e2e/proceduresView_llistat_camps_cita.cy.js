// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('ProceduresView', () => {
  before (() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
      cy.get('.app-menu__list > :nth-child(5)').click()
      cy.url().should('include', '/vtramit')
    })
  })

  it("Hauria de poder crear una cita vàlida i després veure que és part de la llista, seleccionar-la i que el contingut sigui el correcte", () =>{
    // const start = Cypress.env('visitStart');
    const end = Cypress.env('visitEnd');

    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1);
    const day = String(today.getDate());
    const hours = String(today.getHours());
    const minutes = String(today.getMinutes());
    const start = `${year}-${month}-${day} ${hours}:${minutes}`;
    
    // Fem clic al botó de crear cites
    cy.get('[data-test="new"]').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)

    // Omplim el formulari de creació de cites
    cy.procedure('4964894',0,0,0,'topic',start,end,'comentari prova')

    cy.stakeholder(0,'4894684f', 'prova', '4539746','prova@prova.com')

    cy.get('[data-test="save"]').click()

    // Esperem que el toast de success s'hagi amagat del tot
    cy.get('.toast-success').should('be.visible')
    cy.get('.toast-success', { timeout: 20000 }).should('not.exist')

    // Re-carreguem la pàgina
    cy.reload()

    // Fem clic a la última pàgina de cites creades.
    // cy.get('.procedures-table').then($body => {
    //   if ($body.find('.last').length) {
    //     cy.get('.last').click()
    //   }
    // })
    
    // Comprovem que els valors de la barra lateral siguin els esperats
    cy.get('#app-sidebar-vue [data-test="cita-id"]').should('have.value', '4964894')
    cy.get('#app-sidebar-vue [data-test="department"]').should('have.value', 'IMH')
    cy.get('#app-sidebar-vue [data-test="user"]').should('have.value', 'imh-01')
    cy.get('#app-sidebar-vue [data-test="topic"] input').should('have.value', 'topic')
    
    cy.get('#app-sidebar-vue [data-test="date"] input').should('have.value', start)
    
    cy.get('#app-sidebar-vue [data-test="name-edit"]').should('have.value', 'prova')
    cy.get('#app-sidebar-vue [data-test="id-edit"]').should('have.value', '4894684f')
    cy.get('#app-sidebar-vue [data-test="phone-edit"]').should('have.value', '4539746')
  }) 
  // TODO: hauríem de fer clic a la última cita creada? Com que és part de tot el test case igual n'hi haurà de posteriors. Això només funciona perquè realment estem a l'edit de la cita en qüestió quan recarreguem la pàgina.
})
