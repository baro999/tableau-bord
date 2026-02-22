// assets/js/app.js

// Variables globales
let currentDirectionFilter = 'Toutes les directions';
let currentPTAFilter = 'Toutes';

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    chargerDirections();
    chargerIndicateurs();
    chargerProblemes();
    chargerProjets();
    chargerPTA();
    chargerPointsFocaux();
    chargerHistorique();
    
    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'n') { e.preventDefault(); ajouterIndicateur(); }
        if (e.ctrlKey && e.key === 'p') { e.preventDefault(); ajouterProbleme(); }
        if (e.ctrlKey && e.key === 'j') { e.preventDefault(); ajouterProjet(); }
        if (e.ctrlKey && e.key === 'a') { e.preventDefault(); ajouterActivitePTA(); }
        if (e.ctrlKey && e.key === 'e') { e.preventDefault(); exporterExcel(); }
    });
});

// ========== APPELS API ==========
async function apiCall(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Erreur de communication avec le serveur', 'error');
        return null;
    }
}

// ========== NOTIFICATIONS ==========
function showNotification(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// ========== DIRECTIONS ==========
function chargerDirections() {
    const directions = window.directions || [];
    const selects = ['indicateur-direction', 'probleme-direction', 'projet-direction', 'pta-direction', 'point-direction'];
    
    selects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.innerHTML = '<option value="">Sélectionner</option>';
            directions.forEach(dir => {
                select.innerHTML += `<option value="${dir}">${dir}</option>`;
            });
        }
    });

    const menu = document.getElementById('direction-menu');
    if (menu) {
        menu.innerHTML = '<li><a class="dropdown-item" href="#" onclick="changerFiltreDirection(\'Toutes les directions\')">Toutes les directions</a></li><li><hr class="dropdown-divider"></li>';
        directions.forEach(dir => {
            menu.innerHTML += `<li><a class="dropdown-item" href="#" onclick="changerFiltreDirection('${dir}')">${dir}</a></li>`;
        });
    }

    const ptaFilter = document.getElementById('pta-direction-filter');
    if (ptaFilter) {
        ptaFilter.innerHTML = '<option value="Toutes">Toutes les directions</option>';
        directions.forEach(dir => {
            ptaFilter.innerHTML += `<option value="${dir}">${dir}</option>`;
        });
    }
}

function changerFiltreDirection(direction) {
    currentDirectionFilter = direction;
    const indicator = document.getElementById('current-direction');
    const indicatorText = document.getElementById('direction-indicator-text');
    if (indicator) indicator.textContent = direction;
    if (indicatorText) indicatorText.textContent = direction;
    chargerIndicateurs();
    chargerProblemes();
}

// ========== INDICATEURS ==========
async function chargerIndicateurs() {
    const tbody = document.getElementById('indicators-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    let indicateurs = await apiCall('api/indicateurs.php');
    if (!indicateurs) indicateurs = [];
    
    if (currentDirectionFilter !== 'Toutes les directions') {
        indicateurs = indicateurs.filter(i => i.direction === currentDirectionFilter);
    }
    
    indicateurs.forEach(ind => {
        const row = document.createElement('tr');
        const statutClass = `statut-${ind.statut.toLowerCase()}`;
        
        const objectifCourt = ind.objectif ? (ind.objectif.length > 30 ? ind.objectif.substring(0, 30) + '...' : ind.objectif) : '-';
        const actionsCourt = ind.actions ? (ind.actions.length > 25 ? ind.actions.substring(0, 25) + '...' : ind.actions) : '-';
        const methodeCourt = ind.methode ? (ind.methode.length > 30 ? ind.methode.substring(0, 30) + '...' : ind.methode) : '-';
        
        row.innerHTML = `
            <td><span class="badge-direction">${ind.direction}</span></td>
            <td><span class="badge-axe">${ind.axe}</span></td>
            <td><span title="${ind.objectif || ''}">${objectifCourt}</span></td>
            <td><strong>${ind.nom}</strong><br><small class="text-muted">${ind.responsable || ''}</small></td>
            <td><span title="${ind.actions || ''}">${actionsCourt}</span></td>
            <td>${ind.valeur} ${ind.unite}</td>
            <td>${ind.cible} ${ind.unite}</td>
            <td>${parseFloat(ind.pourcentage).toFixed(1)}%</td>
            <td><span class="${statutClass}">${ind.statut}</span></td>
            <td><span class="methode-calcul" title="${ind.methode || ''}">${methodeCourt}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editerIndicateur(${ind.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="supprimerIndicateur(${ind.id})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    const kpi = document.getElementById('kpi-indicateurs');
    if (kpi) kpi.textContent = indicateurs.length;
}

function ajouterIndicateur() {
    document.getElementById('modal-indicateur-title').textContent = 'Ajouter un indicateur';
    document.getElementById('form-indicateur').reset();
    document.getElementById('indicateur-id').value = '';
    if (currentDirectionFilter !== 'Toutes les directions') {
        document.getElementById('indicateur-direction').value = currentDirectionFilter;
    }
    new bootstrap.Modal(document.getElementById('modal-indicateur')).show();
}

async function editerIndicateur(id) {
    const indicateurs = await apiCall('api/indicateurs.php');
    const ind = indicateurs.find(i => i.id == id);
    if (!ind) return;
    
    document.getElementById('modal-indicateur-title').textContent = 'Modifier un indicateur';
    document.getElementById('indicateur-id').value = ind.id;
    document.getElementById('indicateur-direction').value = ind.direction;
    document.getElementById('indicateur-axe').value = ind.axe;
    document.getElementById('indicateur-objectif').value = ind.objectif || '';
    document.getElementById('indicateur-nom').value = ind.nom;
    document.getElementById('indicateur-actions').value = ind.actions || '';
    document.getElementById('indicateur-valeur').value = ind.valeur;
    document.getElementById('indicateur-cible').value = ind.cible;
    document.getElementById('indicateur-unite').value = ind.unite;
    document.getElementById('indicateur-methode').value = ind.methode || '';
    document.getElementById('indicateur-responsable').value = ind.responsable || '';
    document.getElementById('indicateur-periodicite').value = ind.periodicite || 'Mensuelle';
    document.getElementById('indicateur-source').value = ind.source || '';
    
    new bootstrap.Modal(document.getElementById('modal-indicateur')).show();
}

async function sauvegarderIndicateur() {
    const id = document.getElementById('indicateur-id').value;
    const direction = document.getElementById('indicateur-direction').value;
    const axe = document.getElementById('indicateur-axe').value;
    const objectif = document.getElementById('indicateur-objectif').value;
    const nom = document.getElementById('indicateur-nom').value;
    const valeur = parseFloat(document.getElementById('indicateur-valeur').value);
    const cible = parseFloat(document.getElementById('indicateur-cible').value);
    
    if (!direction || !axe || !objectif || !nom || isNaN(valeur) || isNaN(cible)) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    const indicateur = {
        id: id ? parseInt(id) : null,
        direction: direction,
        axe: axe,
        objectif: objectif,
        nom: nom,
        actions: document.getElementById('indicateur-actions').value,
        valeur: valeur,
        cible: cible,
        unite: document.getElementById('indicateur-unite').value,
        methode: document.getElementById('indicateur-methode').value,
        responsable: document.getElementById('indicateur-responsable').value,
        periodicite: document.getElementById('indicateur-periodicite').value,
        source: document.getElementById('indicateur-source').value
    };
    
    let result;
    if (id) {
        result = await apiCall('api/indicateurs.php', 'PUT', indicateur);
    } else {
        result = await apiCall('api/indicateurs.php', 'POST', indicateur);
    }
    
    if (result && result.success) {
        bootstrap.Modal.getInstance(document.getElementById('modal-indicateur')).hide();
        chargerIndicateurs();
        showNotification('Indicateur enregistré avec succès');
    }
}

async function supprimerIndicateur(id) {
    if (confirm('Supprimer cet indicateur ?')) {
        const result = await apiCall(`api/indicateurs.php?id=${id}`, 'DELETE');
        if (result && result.success) {
            chargerIndicateurs();
            showNotification('Indicateur supprimé');
        }
    }
}

// ========== PROBLÈMES ==========
async function chargerProblemes() {
    const tbody = document.getElementById('problemes-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    let problemes = await apiCall('api/problemes.php');
    if (!problemes) problemes = [];
    
    if (currentDirectionFilter !== 'Toutes les directions') {
        problemes = problemes.filter(p => p.direction === currentDirectionFilter);
    }
    
    problemes.forEach((p, index) => {
        const row = document.createElement('tr');
        const statutClass = p.statut === 'Résolu' ? 'statut-resolu' : 
                           p.statut === 'En cours' ? 'statut-encours' : 'statut-nonresolu';
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><span class="badge-direction">${p.direction}</span></td>
            <td>${p.contrainte}</td>
            <td>${p.solution || '-'}</td>
            <td>${p.echeance || '-'}</td>
            <td>${p.responsable || '-'}</td>
            <td>${p.incidence || 'Non'}</td>
            <td>${p.montant || '-'}</td>
            <td><span class="${statutClass}">${p.statut}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editerProbleme(${p.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="supprimerProbleme(${p.id})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    const kpi = document.getElementById('kpi-problemes');
    if (kpi) kpi.textContent = problemes.length;
}

function ajouterProbleme() {
    document.getElementById('form-probleme').reset();
    document.getElementById('probleme-id').value = '';
    if (currentDirectionFilter !== 'Toutes les directions') {
        document.getElementById('probleme-direction').value = currentDirectionFilter;
    }
    document.getElementById('probleme-statut').value = 'En cours';
    new bootstrap.Modal(document.getElementById('modal-probleme')).show();
}

async function editerProbleme(id) {
    const problemes = await apiCall('api/problemes.php');
    const p = problemes.find(p => p.id == id);
    if (!p) return;
    
    document.getElementById('probleme-id').value = p.id;
    document.getElementById('probleme-direction').value = p.direction;
    document.getElementById('probleme-contrainte').value = p.contrainte;
    document.getElementById('probleme-solution').value = p.solution || '';
    document.getElementById('probleme-echeance').value = p.echeance || '';
    document.getElementById('probleme-responsable').value = p.responsable || '';
    document.getElementById('probleme-incidence').value = p.incidence || 'Non';
    document.getElementById('probleme-montant').value = p.montant || '';
    document.getElementById('probleme-statut').value = p.statut;
    
    new bootstrap.Modal(document.getElementById('modal-probleme')).show();
}

async function sauvegarderProbleme() {
    const id = document.getElementById('probleme-id').value;
    const direction = document.getElementById('probleme-direction').value;
    const contrainte = document.getElementById('probleme-contrainte').value;
    
    if (!direction || !contrainte) {
        showNotification('Veuillez remplir les champs obligatoires', 'error');
        return;
    }
    
    const probleme = {
        id: id ? parseInt(id) : null,
        direction: direction,
        contrainte: contrainte,
        solution: document.getElementById('probleme-solution').value,
        echeance: document.getElementById('probleme-echeance').value,
        responsable: document.getElementById('probleme-responsable').value,
        incidence: document.getElementById('probleme-incidence').value,
        montant: document.getElementById('probleme-montant').value,
        statut: document.getElementById('probleme-statut').value
    };
    
    let result;
    if (id) {
        result = await apiCall('api/problemes.php', 'PUT', probleme);
    } else {
        result = await apiCall('api/problemes.php', 'POST', probleme);
    }
    
    if (result && result.success) {
        bootstrap.Modal.getInstance(document.getElementById('modal-probleme')).hide();
        chargerProblemes();
        showNotification('Problème enregistré');
    }
}

async function supprimerProbleme(id) {
    if (confirm('Supprimer ce problème ?')) {
        const result = await apiCall(`api/problemes.php?id=${id}`, 'DELETE');
        if (result && result.success) {
            chargerProblemes();
            showNotification('Problème supprimé');
        }
    }
}

// ========== PROJETS ==========
async function chargerProjets() {
    const container = document.getElementById('projets-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    let projets = await apiCall('api/projets.php');
    if (!projets) projets = [];
    
    if (projets.length === 0) {
        container.innerHTML = `
            <div class="project-empty-state">
                <i class="fas fa-folder-open"></i>
                <h5>Aucun projet</h5>
                <p class="text-muted">Cliquez sur "Nouveau projet" pour créer votre premier projet</p>
            </div>
        `;
        return;
    }
    
    projets.forEach(projet => {
        const card = document.createElement('div');
        card.className = 'project-card-modern';
        
        const getInitials = (nom, prenom) => {
            return (nom?.charAt(0) || '') + (prenom?.charAt(0) || '');
        };
        
        card.innerHTML = `
            <div class="project-header">
                <div class="project-title">
                    <i class="fas fa-project-diagram"></i>
                    <span>${projet.titre}</span>
                </div>
                <div class="project-badges">
                    <span class="project-badge">
                        <i class="fas fa-building"></i> ${projet.direction}
                    </span>
                    <span class="project-badge">
                        <i class="fas fa-tag"></i> ${projet.phase || 'En cours'}
                    </span>
                </div>
                <div class="project-actions">
                    <button class="btn btn-light" onclick="editerProjet(${projet.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-light" onclick="supprimerProjet(${projet.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="project-content">
                <div class="project-info-grid">
                    <div class="project-info-item">
                        <div class="project-info-label">Zone</div>
                        <div class="project-info-value">${projet.zone || 'Non spécifiée'}</div>
                    </div>
                    <div class="project-info-item">
                        <div class="project-info-label">Durée</div>
                        <div class="project-info-value">${projet.duree || 'Non spécifiée'}</div>
                    </div>
                    <div class="project-info-item">
                        <div class="project-info-label">Maîtrise d'ouvrage</div>
                        <div class="project-info-value">${projet.maitrise_ouvrage || 'Non spécifiée'}</div>
                    </div>
                    <div class="project-info-item">
                        <div class="project-info-label">Budget</div>
                        <div class="project-info-value">${projet.budget || '0'} FCFA</div>
                    </div>
                </div>
                
                <div class="project-info-item" style="margin-bottom: 20px;">
                    <div class="project-info-label">Objectif global</div>
                    <div class="project-info-value">${projet.objectif || 'Non spécifié'}</div>
                </div>
                
                <div class="project-progress-container">
                    <div class="project-progress-header">
                        <span class="project-progress-label">Avancement global</span>
                        <span class="project-progress-percent">${projet.avancement}%</span>
                    </div>
                    <div class="project-progress-bar">
                        <div class="project-progress-fill" style="width: ${projet.avancement}%"></div>
                    </div>
                </div>
                
                <div class="project-sections">
                    <div class="project-section-card">
                        <div class="project-section-header">
                            <i class="fas fa-file-contract"></i>
                            <span>Marchés (${projet.marches?.length || 0})</span>
                        </div>
                        <div class="project-section-body">
                            ${projet.marches?.length > 0 ? projet.marches.map(m => `
                                <div class="project-market-item">
                                    <div class="project-market-title">${m.marche || 'Marché'}</div>
                                    <div class="project-market-details">
                                        <span><i class="fas fa-user-tie"></i> ${m.titulaire || '-'}</span>
                                        <span><i class="fas fa-coins"></i> ${m.montant || '-'}</span>
                                        ${m.bailleur ? `<span><i class="fas fa-hand-holding-usd"></i> ${m.bailleur}</span>` : ''}
                                    </div>
                                </div>
                            `).join('') : '<p class="text-muted text-center p-3">Aucun marché</p>'}
                        </div>
                    </div>
                    
                    <div class="project-section-card">
                        <div class="project-section-header">
                            <i class="fas fa-users"></i>
                            <span>Équipe projet (${projet.equipe?.length || 0})</span>
                        </div>
                        <div class="project-section-body">
                            ${projet.equipe?.length > 0 ? projet.equipe.map(e => `
                                <div class="project-team-item">
                                    <div class="project-team-avatar">
                                        ${getInitials(e.nom, e.prenom)}
                                    </div>
                                    <div class="project-team-info">
                                        <div class="project-team-name">${e.nom || ''} ${e.prenom || ''}</div>
                                        <div class="project-team-role">${e.poste || ''}</div>
                                    </div>
                                </div>
                            `).join('') : '<p class="text-muted text-center p-3">Aucun membre</p>'}
                        </div>
                    </div>
                </div>
                
                ${projet.description ? `
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">Description</small>
                        <p class="mb-0">${projet.description}</p>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.appendChild(card);
    });
    
    const kpi = document.getElementById('kpi-projets');
    if (kpi) kpi.textContent = projets.length;
}

let marcheCount = 0;
let equipeCount = 0;

function ajouterProjet() {
    document.getElementById('form-projet').reset();
    document.getElementById('projet-id').value = '';
    document.getElementById('marches-container').innerHTML = '';
    document.getElementById('equipe-container').innerHTML = '';
    marcheCount = 0;
    equipeCount = 0;
    ajouterMarche();
    ajouterMembre();
    new bootstrap.Modal(document.getElementById('modal-projet')).show();
}

async function editerProjet(id) {
    const projets = await apiCall('api/projets.php');
    const projet = projets.find(p => p.id == id);
    if (!projet) return;
    
    document.getElementById('projet-id').value = projet.id;
    document.getElementById('projet-titre').value = projet.titre;
    document.getElementById('projet-direction').value = projet.direction;
    document.getElementById('projet-phase').value = projet.phase || '';
    document.getElementById('projet-duree').value = projet.duree || '';
    document.getElementById('projet-zone').value = projet.zone || '';
    document.getElementById('projet-description').value = projet.description || '';
    document.getElementById('projet-objectif').value = projet.objectif || '';
    document.getElementById('projet-mo').value = projet.maitrise_ouvrage || '';
    document.getElementById('projet-date-debut').value = projet.date_debut || '';
    document.getElementById('projet-date-fin').value = projet.date_fin || '';
    document.getElementById('projet-budget').value = projet.budget || '';
    document.getElementById('projet-avancement').value = projet.avancement || 0;
    
    document.getElementById('marches-container').innerHTML = '';
    if (projet.marches && projet.marches.length > 0) {
        projet.marches.forEach(m => {
            ajouterMarche(m);
        });
    } else {
        ajouterMarche();
    }
    
    document.getElementById('equipe-container').innerHTML = '';
    if (projet.equipe && projet.equipe.length > 0) {
        projet.equipe.forEach(e => {
            ajouterMembre(e);
        });
    } else {
        ajouterMembre();
    }
    
    new bootstrap.Modal(document.getElementById('modal-projet')).show();
}

function ajouterMarche(marche = null) {
    const id = marcheCount++;
    const container = document.getElementById('marches-container');
    const div = document.createElement('div');
    div.className = 'card mb-2 p-2';
    div.id = `marche-${id}`;
    div.innerHTML = `
        <div class="row">
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Marché" id="marche-nom-${id}" value="${marche?.marche || ''}"></div>
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Titulaire" id="marche-titulaire-${id}" value="${marche?.titulaire || ''}"></div>
            <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="Montant" id="marche-montant-${id}" value="${marche?.montant || ''}"></div>
            <div class="col-md-1"><button class="btn btn-sm btn-danger" onclick="supprimerMarche('${id}')"><i class="fas fa-times"></i></button></div>
        </div>
    `;
    container.appendChild(div);
}

function supprimerMarche(id) {
    const element = document.getElementById(`marche-${id}`);
    if (element) element.remove();
}

function ajouterMembre(membre = null) {
    const id = equipeCount++;
    const container = document.getElementById('equipe-container');
    const div = document.createElement('div');
    div.className = 'card mb-2 p-2';
    div.id = `membre-${id}`;
    div.innerHTML = `
        <div class="row">
            <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Poste" id="membre-poste-${id}" value="${membre?.poste || ''}"></div>
            <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="Nom" id="membre-nom-${id}" value="${membre?.nom || ''}"></div>
            <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="Prénom" id="membre-prenom-${id}" value="${membre?.prenom || ''}"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-danger" onclick="supprimerMembre('${id}')"><i class="fas fa-times"></i></button></div>
        </div>
    `;
    container.appendChild(div);
}

function supprimerMembre(id) {
    const element = document.getElementById(`membre-${id}`);
    if (element) element.remove();
}

async function sauvegarderProjet() {
    const id = document.getElementById('projet-id').value;
    const titre = document.getElementById('projet-titre').value;
    
    if (!titre) {
        showNotification('Le titre du projet est obligatoire', 'error');
        return;
    }
    
    const marches = [];
    for (let i = 0; i < marcheCount; i++) {
        const elem = document.getElementById(`marche-${i}`);
        if (elem) {
            const nom = document.getElementById(`marche-nom-${i}`)?.value;
            const titulaire = document.getElementById(`marche-titulaire-${i}`)?.value;
            const montant = document.getElementById(`marche-montant-${i}`)?.value;
            if (nom || titulaire || montant) {
                marches.push({
                    marche: nom || '',
                    titulaire: titulaire || '',
                    montant: montant || ''
                });
            }
        }
    }
    
    const equipe = [];
    for (let i = 0; i < equipeCount; i++) {
        const elem = document.getElementById(`membre-${i}`);
        if (elem) {
            const poste = document.getElementById(`membre-poste-${i}`)?.value;
            const nom = document.getElementById(`membre-nom-${i}`)?.value;
            const prenom = document.getElementById(`membre-prenom-${i}`)?.value;
            if (poste || nom) {
                equipe.push({
                    poste: poste || '',
                    nom: nom || '',
                    prenom: prenom || ''
                });
            }
        }
    }
    
    const projet = {
        id: id ? parseInt(id) : null,
        titre: titre,
        direction: document.getElementById('projet-direction').value,
        phase: document.getElementById('projet-phase').value,
        duree: document.getElementById('projet-duree').value,
        zone: document.getElementById('projet-zone').value,
        description: document.getElementById('projet-description').value,
        objectif: document.getElementById('projet-objectif').value,
        maitrise_ouvrage: document.getElementById('projet-mo').value,
        date_debut: document.getElementById('projet-date-debut').value,
        date_fin: document.getElementById('projet-date-fin').value,
        budget: document.getElementById('projet-budget').value,
        avancement: parseInt(document.getElementById('projet-avancement').value) || 0,
        marches: marches,
        equipe: equipe
    };
    
    let result;
    if (id) {
        result = await apiCall('api/projets.php', 'PUT', projet);
    } else {
        result = await apiCall('api/projets.php', 'POST', projet);
    }
    
    if (result && result.success) {
        bootstrap.Modal.getInstance(document.getElementById('modal-projet')).hide();
        chargerProjets();
        showNotification('Projet enregistré');
    }
}

async function supprimerProjet(id) {
    if (confirm('Supprimer ce projet ?')) {
        const result = await apiCall(`api/projets.php?id=${id}`, 'DELETE');
        if (result && result.success) {
            chargerProjets();
            showNotification('Projet supprimé');
        }
    }
}

// ========== PTA ==========
function filtrerPTA() {
    currentPTAFilter = document.getElementById('pta-direction-filter').value;
    chargerPTA();
}

async function chargerPTA() {
    const tbody = document.getElementById('pta-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    let activites = await apiCall('api/pta.php');
    if (!activites) activites = [];
    
    if (currentPTAFilter !== 'Toutes') {
        activites = activites.filter(a => a.direction === currentPTAFilter);
    }
    
    activites.forEach(a => {
        let statutClass = a.statut === 'Réalisé' ? 'statut-realise' : 
                         a.statut === 'En cours' ? 'statut-encours' :
                         a.statut === 'Retard' ? 'statut-retard' : 'statut-nonlance';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><span class="badge-direction">${a.direction}</span></td>
            <td>${a.reference || ''}</td>
            <td>${a.activite}</td>
            <td>${a.livrable || ''}</td>
            <td>${a.montant || '-'}</td>
            <td>${a.responsable || ''}</td>
            <td>${a.date_debut || ''}</td>
            <td>${a.date_fin || ''}</td>
            <td>${a.avancement}%</td>
            <td><span class="${statutClass}">${a.statut}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editerActivitePTA(${a.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="supprimerActivitePTA(${a.id})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    const kpi = document.getElementById('kpi-activites');
    if (kpi) kpi.textContent = activites.length;
}

function ajouterActivitePTA() {
    document.getElementById('form-pta').reset();
    document.getElementById('pta-id').value = '';
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('pta-debut').value = today;
    new bootstrap.Modal(document.getElementById('modal-pta')).show();
}

async function editerActivitePTA(id) {
    const activites = await apiCall('api/pta.php');
    const a = activites.find(a => a.id == id);
    if (!a) return;
    
    document.getElementById('pta-id').value = a.id;
    document.getElementById('pta-direction').value = a.direction;
    document.getElementById('pta-ref').value = a.reference || '';
    document.getElementById('pta-activite').value = a.activite;
    document.getElementById('pta-livrable').value = a.livrable || '';
    document.getElementById('pta-montant').value = a.montant || '';
    document.getElementById('pta-responsable').value = a.responsable || '';
    document.getElementById('pta-debut').value = a.date_debut || '';
    document.getElementById('pta-fin').value = a.date_fin || '';
    document.getElementById('pta-avancement').value = a.avancement || 0;
    document.getElementById('pta-statut').value = a.statut;
    
    new bootstrap.Modal(document.getElementById('modal-pta')).show();
}

async function sauvegarderActivitePTA() {
    const id = document.getElementById('pta-id').value;
    const direction = document.getElementById('pta-direction').value;
    const activite = document.getElementById('pta-activite').value;
    
    if (!direction || !activite) {
        showNotification('Veuillez remplir les champs obligatoires', 'error');
        return;
    }
    
    const avancement = parseInt(document.getElementById('pta-avancement').value) || 0;
    
    const activiteObj = {
        id: id ? parseInt(id) : null,
        direction: direction,
        reference: document.getElementById('pta-ref').value,
        activite: activite,
        livrable: document.getElementById('pta-livrable').value,
        montant: document.getElementById('pta-montant').value || '-',
        responsable: document.getElementById('pta-responsable').value,
        date_debut: document.getElementById('pta-debut').value,
        date_fin: document.getElementById('pta-fin').value,
        avancement: avancement
    };
    
    let result;
    if (id) {
        result = await apiCall('api/pta.php', 'PUT', activiteObj);
    } else {
        result = await apiCall('api/pta.php', 'POST', activiteObj);
    }
    
    if (result && result.success) {
        bootstrap.Modal.getInstance(document.getElementById('modal-pta')).hide();
        chargerPTA();
        showNotification('Activité enregistrée');
    }
}

async function supprimerActivitePTA(id) {
    if (confirm('Supprimer cette activité ?')) {
        const result = await apiCall(`api/pta.php?id=${id}`, 'DELETE');
        if (result && result.success) {
            chargerPTA();
            showNotification('Activité supprimée');
        }
    }
}

// ========== POINTS FOCAUX ==========
async function chargerPointsFocaux() {
    const tbody = document.getElementById('points-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    let points = await apiCall('api/points.php');
    if (!points) points = [];
    
    points.sort((a, b) => a.direction.localeCompare(b.direction));
    
    points.forEach((p, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><span class="badge-point">${p.direction}</span></td>
            <td><strong>${p.nom}</strong></td>
            <td>
                ${p.email ? `<i class="fas fa-envelope me-1"></i>${p.email}<br>` : ''}
                ${p.telephone ? `<i class="fas fa-phone me-1"></i>${p.telephone}` : ''}
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editerPointFocal(${p.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="supprimerPointFocal(${p.id})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

async function ajouterPointFocal() {
    // Réinitialiser le formulaire
    document.getElementById('form-point').reset();
    document.getElementById('point-id').value = '';
    
    // Réactiver le bouton
    document.querySelector('#modal-point .btn-point').disabled = false;
    
    // Cacher l'alerte
    document.getElementById('point-direction-alert').style.display = 'none';
    
    // Mettre à jour la liste des directions disponibles
    await mettreAJourListeDirections();
    
    new bootstrap.Modal(document.getElementById('modal-point')).show();
}

async function mettreAJourListeDirections() {
    try {
        // Récupérer les points focaux existants
        const points = await apiCall('api/points.php');
        const directionsUtilisees = points.map(p => p.direction);
        
        // Récupérer toutes les directions
        const select = document.getElementById('point-direction');
        const options = select.options;
        
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value && directionsUtilisees.includes(option.value)) {
                option.disabled = true;
                option.textContent = option.value + ' (déjà utilisé)';
            } else {
                option.disabled = false;
                option.textContent = option.value;
            }
        }
    } catch (error) {
        console.error('Erreur lors de la mise à jour des directions:', error);
    }
}

async function editerPointFocal(id) {
    const points = await apiCall('api/points.php');
    const p = points.find(p => p.id == id);
    if (!p) return;
    
    document.getElementById('point-id').value = p.id;
    document.getElementById('point-direction').value = p.direction;
    document.getElementById('point-nom').value = p.nom;
    document.getElementById('point-email').value = p.email || '';
    document.getElementById('point-telephone').value = p.telephone || '';
    
    // En mode édition, on désactive la vérification
    document.getElementById('point-direction-alert').style.display = 'none';
    document.querySelector('#modal-point .btn-point').disabled = false;
    
    new bootstrap.Modal(document.getElementById('modal-point')).show();
}

async function editerPointFocal(id) {
    const points = await apiCall('api/points.php');
    const p = points.find(p => p.id == id);
    if (!p) return;
    
    document.getElementById('point-id').value = p.id;
    document.getElementById('point-direction').value = p.direction;
    document.getElementById('point-nom').value = p.nom;
    document.getElementById('point-email').value = p.email || '';
    document.getElementById('point-telephone').value = p.telephone || '';
    
    new bootstrap.Modal(document.getElementById('modal-point')).show();
}



async function supprimerPointFocal(id) {
    if (confirm('Supprimer ce point focal ?')) {
        const result = await apiCall(`api/points.php?id=${id}`, 'DELETE');
        if (result && result.success) {
            chargerPointsFocaux();
            showNotification('Point focal supprimé');
        }
    }
}
// Dans assets/js/app.js, remplacez la fonction sauvegarderPointFocal par celle-ci :

// Dans assets/js/app.js, remplacez complètement la fonction sauvegarderPointFocal

async function sauvegarderPointFocal() {
    console.log('=== DÉBUT SAUVEGARDE POINT FOCAL ===');
    
    try {
        // Récupérer les valeurs du formulaire
        const id = document.getElementById('point-id')?.value || '';
        const direction = document.getElementById('point-direction')?.value;
        const nom = document.getElementById('point-nom')?.value;
        const email = document.getElementById('point-email')?.value;
        const telephone = document.getElementById('point-telephone')?.value;
        
        console.log('Valeurs récupérées:', { id, direction, nom, email, telephone });
        
        // Validation
        if (!direction || !nom) {
            showNotification('La direction et le nom sont obligatoires', 'error');
            return;
        }
        
        // Préparer les données
        const pointData = {
            id: id ? parseInt(id) : null,
            direction: direction,
            nom: nom,
            email: email || null,
            telephone: telephone || null
        };
        
        console.log('Données à envoyer:', pointData);
        
        // Déterminer l'URL et la méthode
        const url = 'api/points.php';
        const method = id ? 'PUT' : 'POST';
        
        console.log(`Envoi ${method} à ${url}`);
        
        // Appel API avec fetch directement pour mieux contrôler
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(pointData)
        });
        
        console.log('Status réponse:', response.status);
        console.log('Headers réponse:', response.headers);
        
        // Lire la réponse comme texte d'abord
        const responseText = await response.text();
        console.log('Réponse brute:', responseText);
        
        // Essayer de parser le JSON
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('JSON parsé:', result);
        } catch (e) {
            console.error('Erreur parsing JSON:', e);
            showNotification('Réponse serveur invalide: ' + responseText.substring(0, 100), 'error');
            return;
        }
        
        // Traiter la réponse
        if (result && result.success === true) {
            // Fermer le modal
            const modal = document.getElementById('modal-point');
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
            
            // Recharger la liste
            await chargerPointsFocaux();
            
            // Notification de succès
            showNotification('Point focal enregistré avec succès', 'success');
        } else {
            // Afficher l'erreur
            const errorMsg = result && result.error ? result.error : 'Erreur inconnue';
            console.error('Erreur retournée:', errorMsg);
            showNotification('Erreur: ' + errorMsg, 'error');
        }
        
    } catch (error) {
        console.error('Exception dans sauvegarderPointFocal:', error);
        showNotification('Erreur de communication: ' + error.message, 'error');
    }
    
    console.log('=== FIN SAUVEGARDE POINT FOCAL ===');
}

// Améliorer aussi la fonction apiCall pour mieux gérer les erreurs
async function apiCall(url, method = 'GET', data = null) {
    console.log(`apiCall: ${method} ${url}`, data);
    
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        console.log(`Réponse ${url}: status ${response.status}`);
        
        const text = await response.text();
        console.log('Réponse texte:', text);
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Erreur parsing JSON:', e);
            throw new Error('Réponse non-JSON reçue');
        }
    } catch (error) {
        console.error('Erreur fetch:', error);
        throw error;
    }
}

// ========== HISTORIQUE ==========
async function chargerHistorique() {
    const filtre = document.getElementById('historique-filtre')?.value || 'all';
    const container = document.getElementById('historique-complet');
    const sidebar = document.getElementById('historique-sidebar');
    
    let historique = await apiCall('api/historique.php');
    if (!historique) historique = [];
    
    let filtres = historique;
    if (filtre !== 'all') {
        filtres = filtres.filter(h => h.module.toLowerCase().includes(filtre.toLowerCase()));
    }
    
    if (sidebar) {
        sidebar.innerHTML = '';
        historique.slice(0, 5).forEach(h => {
            const div = document.createElement('div');
            div.className = 'historique-item small';
            div.innerHTML = `
                <div><strong>${new Date(h.date_action).toLocaleString('fr-FR')}</strong></div>
                <div><span class="badge bg-primary">${h.module}</span> <span class="badge bg-secondary">${h.action}</span></div>
                <div>${h.details}</div>
                <div><small class="text-muted">par ${h.nom_complet || 'Système'}</small></div>
            `;
            sidebar.appendChild(div);
        });
    }
    
    if (container) {
        container.innerHTML = '';
        filtres.forEach(h => {
            const div = document.createElement('div');
            div.className = 'historique-item';
            div.innerHTML = `
                <div class="d-flex justify-content-between">
                    <strong>${new Date(h.date_action).toLocaleString('fr-FR')}</strong>
                    <span><span class="badge bg-primary">${h.module}</span> <span class="badge bg-secondary">${h.action}</span></span>
                </div>
                <div class="mt-2">${h.details}</div>
                <div><small class="text-muted">par ${h.nom_complet || 'Système'}</small></div>
            `;
            container.appendChild(div);
        });
        
        if (filtres.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">Aucun historique</p>';
        }
    }
}

// ========== EXPORTS ==========
function exporterPDF() {
    window.location.href = 'api/export.php?format=pdf';
}

function exporterExcel() {
    window.location.href = 'api/export.php?format=excel';
}

function exporterCSV() {
    window.location.href = 'api/export.php?format=csv';
}

function exporterModule(module) {
    window.location.href = `api/export.php?format=csv&module=${module}`;
}

function imprimer() {
    window.print();
}

// Exposer les fonctions globales
window.ajouterIndicateur = ajouterIndicateur;
window.editerIndicateur = editerIndicateur;
window.supprimerIndicateur = supprimerIndicateur;
window.sauvegarderIndicateur = sauvegarderIndicateur;

window.ajouterProbleme = ajouterProbleme;
window.editerProbleme = editerProbleme;
window.supprimerProbleme = supprimerProbleme;
window.sauvegarderProbleme = sauvegarderProbleme;

window.ajouterProjet = ajouterProjet;
window.editerProjet = editerProjet;
window.supprimerProjet = supprimerProjet;
window.sauvegarderProjet = sauvegarderProjet;
window.ajouterMarche = ajouterMarche;
window.supprimerMarche = supprimerMarche;
window.ajouterMembre = ajouterMembre;
window.supprimerMembre = supprimerMembre;

window.filtrerPTA = filtrerPTA;
window.ajouterActivitePTA = ajouterActivitePTA;
window.editerActivitePTA = editerActivitePTA;
window.supprimerActivitePTA = supprimerActivitePTA;
window.sauvegarderActivitePTA = sauvegarderActivitePTA;

window.ajouterPointFocal = ajouterPointFocal;
window.editerPointFocal = editerPointFocal;
window.supprimerPointFocal = supprimerPointFocal;
window.sauvegarderPointFocal = sauvegarderPointFocal;

window.exporterPDF = exporterPDF;
window.exporterExcel = exporterExcel;
window.exporterCSV = exporterCSV;
window.exporterModule = exporterModule;
window.imprimer = imprimer;

window.changerFiltreDirection = changerFiltreDirection;