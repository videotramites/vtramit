// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

describe('ProceduresView', () => {
  beforeEach(() => {
    cy.fixture('cypress.json').then((data) =>{
      cy.viewport(1920, 1080)
      cy.visit(data.route)
      cy.login(data.user, data.password)
      cy.url().should('include', '/dashboard')
      cy.get('.app-menu__list > :nth-child(5)').click()
      cy.url().should('include', '/vtramit')
    })
  })

  it("Creem cites i comprovem la funcionalitat dels filtres del llistat", () =>{
    const start = Cypress.env('visitStart');
    const end = Cypress.env('visitEnd');
  
    // Creem 10 cites
    Cypress._.times(9, (i) => {
      cy.get('.action-item.icon-add').should('be.visible').click()
      cy.get('[data-test="procedure"]').select(0)
      
      cy.procedure(`AUTO-${i}`,0,0,0,`topic ${i}`,start,end,'Comentari prova')
      cy.stakeholder(0, `id-${i}`, `prova ${i}`, `4539746${i}`, `prova${i}@prova.com`)
      cy.get('[data-test="save"]').click()
      // Esperem que el toast de success s'hagi amagat del tot
      cy.get('.toast-success').should('be.visible')
      cy.get('.toast-success', { timeout: 20000 }).should('not.exist')
    })

    cy.get('.action-item.icon-add').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure(`AUTO-10`,0,0,0,`topic 10`,'2025-10-17 12:25','2025-10-17 13:25','Comentari prova')
    cy.stakeholder(0, `id-10`, `prova 10`, `453974610`, `prova10@prova.com`)
    
    cy.reload()
    cy.get('.action-item.icon-add').should('be.visible').click()
    cy.get('[data-test="procedure"]').select(0)
    cy.procedure(`AUTO-11`,0,0,0,`topic 11`,'2025-10-17 12:25','2025-10-17 13:25','Comentari prova')
    
    cy.stakeholder(0, `id-11`, `prova 11`, `453974611`, `prova11@prova.com`)
  })
  // TODO: Aquí no s'està fent res amb aquests formularis, quina és la intenció?

  // TODO: Aquests tests realment no comproven que la funcionalitat estigui bé, de fet només fan clic i ja.
  // TODO: Aquests tests no es poden estructurar així: si s'executessin en paral·lel no tindrien les dades necessaries disponibles.
  it("Hauria de funcionar el filtre de la id", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get('#filter_free_search').type("AUTO-1")
    cy.get('[data-test="apply"]').click()
  })
  it("Hauria de funcionar el filtre de la id de l'usuari", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get('#filter_citizenId').type("id-2")
    cy.get('[data-test="apply"]').click()
  })
  it("Hauria de funcionar el filtre del nom", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get('#filter_name').type("prova 3")
    cy.get('[data-test="apply"]').click()
  })
  it("Hauria de funcionar el filtre del numero de telefon", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get('#filter_phone').type("45397464")
    cy.get('[data-test="apply"]').click()
  })
  it("Hauria de funcionar el filtre del email", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get('#filter_email').type("prova5@prova.com")
    cy.get('[data-test="apply"]').click()
  })
  it("Hauria de funcionar el filtre de la data inicial", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get(':nth-child(1) > :nth-child(1) > .mx-datepicker > .mx-input-wrapper > .mx-input').type("2025-10-01 12:25")
    cy.get('[data-test="apply"]').click()
  })
  it("Hauria de funcionar el filtre de la data fi", () =>{
    cy.get('[data-test="filter"]').click()
    cy.get(':nth-child(2) > :nth-child(1) > .mx-datepicker > .mx-input-wrapper > .mx-input').type("2025-11-30 12:25")
    cy.get('[data-test="apply"]').click()
  })
})
