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

  it("Creem cites i comprovem la funcionalitat d'ordenació del llistat", () =>{
    const start = Cypress.env('visitStart');
    const end = Cypress.env('visitEnd');
  
    Cypress._.times(10, (i) => {
      cy.get('[data-test="new"]').should('be.visible').click()
      cy.get('[data-test="procedure"]').select(0)
      cy.procedure(`AUTO-${i}`,0,0,0,`topic ${i}`,start,end,'Comentari prova')
      cy.stakeholder(0, `id-${i}`, `prova ${i}`, `4539746${i}`, `prova${i}@prova.com`)
      cy.get('[data-test="save"]').click()
      // Esperem que el toast de success s'hagi amagat del tot
      cy.get('.toast-success').should('be.visible')
      cy.get('.toast-success', { timeout: 20000 }).should('not.exist')
    })
  })

  // TODO: Aquests tests no es poden estructurar així: si s'executessin en paral·lel no tindrien les dades necessaries disponibles.
  it("El llistat està ordenat per id de cita", () =>{
    cy.get('[data-test="cita-id"]')
    .should($text => {
      const texts = [...$text].map(txt => txt.innerText.trim());
      const sorted = [...texts].sort();
      expect(texts).to.deep.equal(sorted);
    });
  })
  // TODO: mirar si això realment funciona, però en cap moment fem res per canviar l'ordenació del llistat, què estem miran t aquí?
})
