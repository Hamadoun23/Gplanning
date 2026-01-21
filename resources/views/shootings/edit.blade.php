@extends('layouts.app')

@section('title', 'Modifier tournage')

@section('content')
    <div class="publication-create-container" data-gsap="fadeIn">
        <!-- Hero Header -->
        <div class="create-hero" data-gsap="fadeInUp">
            <div class="hero-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </div>
            <div class="hero-content">
                <h1 class="hero-title">Modifier tournage</h1>
                <p class="hero-subtitle">Modifiez les informations de ce tournage</p>
            </div>
        </div>
        
        <!-- Main Form Card -->
        <div class="form-card-modern" data-gsap="fadeInUp">
            <form action="{{ route('shootings.update', $shooting) }}" method="POST" id="shooting-form" class="publication-form-modern">
                @csrf
                @method('PUT')
                
                @php
                    $month = request()->get('return_month', $shooting->date->month);
                    $year = request()->get('return_year', $shooting->date->year);
                @endphp
                <input type="hidden" name="return_month" value="{{ $month }}">
                <input type="hidden" name="return_year" value="{{ $year }}">
                
                <!-- Client Selection -->
                <div class="form-field-modern" data-gsap="fadeInUp">
                    <label for="client_id" class="form-label-modern">
                        <span class="label-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                        <span class="label-text">Client <span class="required-star">*</span></span>
                    </label>
                    <div class="select-wrapper-modern">
                        <select id="client_id" name="client_id" required class="select-modern">
                            <option value="">Sélectionner un client</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ $shooting->client_id == $c->id ? 'selected' : '' }}>{{ $c->nom_entreprise }}</option>
                            @endforeach
                        </select>
                        <div class="select-arrow">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 4l4 4 4-4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Date Selection -->
                <div class="form-field-modern" data-gsap="fadeInUp">
                    <label for="date" class="form-label-modern">
                        <span class="label-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </span>
                        <span class="label-text">Date et heure <span class="required-star">*</span></span>
                    </label>
                    <div class="input-wrapper-modern">
                        <input type="datetime-local" id="date" name="date" value="{{ $shooting->date ? $shooting->date->format('Y-m-d\TH:i') : '' }}" required class="input-modern" data-realtime-check data-check-type="shooting" data-exclude-id="{{ $shooting->id }}">
                        <div class="input-focus-line"></div>
                    </div>
                    <div id="date-warnings" class="warnings-container"></div>
                </div>
                
                <!-- Description -->
                <div class="form-field-modern" data-gsap="fadeInUp">
                    <label for="description" class="form-label-modern">
                        <span class="label-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </span>
                        <span class="label-text">Description</span>
                    </label>
                    <div class="textarea-wrapper-modern">
                        <textarea id="description" name="description" rows="5" class="textarea-modern" placeholder="Décrivez le tournage (optionnel)...">{{ $shooting->description ?? '' }}</textarea>
                        <div class="textarea-focus-line"></div>
                    </div>
                    <div class="char-counter">
                        <span id="char-count">0</span> / 500 caractères
                    </div>
                </div>
                
                <!-- Content Idea Selection -->
                <div class="form-field-modern" data-gsap="fadeInUp">
                    <label for="content_idea_id" class="form-label-modern">
                        <span class="label-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        </span>
                        <span class="label-text">Idée de contenu <span class="required-star">*</span></span>
                    </label>
                    <div class="select-wrapper-modern">
                        <select id="content_idea_id" name="content_idea_id" required class="select-modern">
                            <option value="">Sélectionner une idée</option>
                            @if($contentIdeas->count() > 0)
                                @foreach($contentIdeas as $idea)
                                    <option value="{{ $idea->id }}" {{ ($shooting->contentIdeas->first()?->id ?? null) == $idea->id ? 'selected' : '' }} data-type="{{ $idea->type }}">{{ $idea->titre }} ({{ $idea->type }})</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="select-arrow">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 4l4 4 4-4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="form-help-text">
                        <a href="{{ route('content-ideas.create') }}" target="_blank" class="help-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            Créer une nouvelle idée de contenu
                        </a>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="form-actions-modern" data-gsap="fadeInUp">
                    <button type="submit" class="btn-primary-modern" id="submit-btn">
                        <span class="btn-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span class="btn-text">Modifier le tournage</span>
                    </button>
                    <a href="{{ route('shootings.index') }}" class="btn-secondary-modern">
                        <span class="btn-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </span>
                        <span class="btn-text">Annuler</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        /* Override GSAP initial opacity */
        .publication-create-container [data-gsap] {
            opacity: 1 !important;
        }
        
        .publication-create-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .create-hero {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(255, 106, 58, 0.1) 0%, rgba(255, 106, 58, 0.05) 100%);
            border-radius: 20px;
            border: 1px solid rgba(255, 106, 58, 0.2);
        }
        
        .hero-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 16px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(255, 106, 58, 0.3);
        }
        
        .hero-content {
            flex: 1;
        }
        
        .hero-title {
            font-size: 2rem;
            font-weight: 700;
            color: #303030;
            margin: 0 0 0.5rem 0;
            background: linear-gradient(135deg, #303030 0%, #FF6A3A 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1rem;
            color: #666;
            margin: 0;
        }
        
        .form-card-modern {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 1;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            margin-top: 2rem;
        }
        
        .publication-form-modern {
            display: flex !important;
            flex-direction: column;
            gap: 2rem;
            width: 100%;
            position: relative;
            z-index: 1;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .form-field-modern {
            display: flex !important;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
            position: relative;
            z-index: 1;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .form-label-modern {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #303030;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .label-icon {
            color: #FF6A3A;
            display: flex;
            align-items: center;
        }
        
        .required-star {
            color: #dc3545;
            font-weight: 700;
        }
        
        .select-wrapper-modern,
        .input-wrapper-modern,
        .textarea-wrapper-modern {
            position: relative;
        }
        
        .select-modern,
        .input-modern,
        .textarea-modern {
            width: 100%;
            padding: 1rem 3rem 1rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            color: #303030;
            background: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            appearance: none;
            outline: none;
        }
        
        .textarea-modern {
            padding: 1rem;
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }
        
        .select-modern:focus,
        .input-modern:focus,
        .textarea-modern:focus {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 4px rgba(255, 106, 58, 0.1);
            transform: translateY(-2px);
        }
        
        .select-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
            transition: color 0.3s;
        }
        
        .select-wrapper-modern:hover .select-arrow,
        .select-wrapper-modern:focus-within .select-arrow {
            color: #FF6A3A;
        }
        
        .input-focus-line,
        .textarea-focus-line {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #FF6A3A 0%, #e55a2a 100%);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
        }
        
        .input-modern:focus ~ .input-focus-line,
        .textarea-modern:focus ~ .textarea-focus-line {
            width: 100%;
        }
        
        .warnings-container {
            margin-top: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .warnings-container .alert {
            padding: 0.875rem 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.5rem;
        }
        
        .form-help-text {
            margin-top: 0.5rem;
        }
        
        .help-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #FF6A3A;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .help-link:hover {
            color: #e55a2a;
            gap: 0.75rem;
        }
        
        .form-actions-modern {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 2rem;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn-primary-modern,
        .btn-secondary-modern {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 106, 58, 0.4);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 106, 58, 0.5);
        }
        
        .btn-primary-modern:active {
            transform: translateY(0);
        }
        
        .btn-secondary-modern {
            background: white;
            color: #303030;
            border: 2px solid #e0e0e0;
        }
        
        .btn-secondary-modern:hover {
            border-color: #303030;
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .btn-icon {
            display: flex;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .publication-create-container {
                padding: 1rem 0;
            }
            
            .create-hero {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }
            
            .hero-title {
                font-size: 1.5rem;
            }
            
            .form-card-modern {
                padding: 1.5rem;
                border-radius: 16px;
            }
            
            .form-actions-modern {
                flex-direction: column;
            }
            
            .btn-primary-modern,
            .btn-secondary-modern {
                width: 100%;
            }
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSelect = document.getElementById('client_id');
            const dateInput = document.getElementById('date');
            const warningsDiv = document.getElementById('date-warnings');
            const form = document.getElementById('shooting-form');
            const descriptionTextarea = document.getElementById('description');
            const charCount = document.getElementById('char-count');
            const submitBtn = document.getElementById('submit-btn');
            const excludeId = dateInput.getAttribute('data-exclude-id');
            
            // Character counter for description
            if (descriptionTextarea && charCount) {
                function updateCharCount() {
                    const count = descriptionTextarea.value.length;
                    charCount.textContent = count;
                    if (count > 500) {
                        charCount.style.color = '#dc3545';
                    } else if (count > 400) {
                        charCount.style.color = '#ffc107';
                    } else {
                        charCount.style.color = '#999';
                    }
                }
                descriptionTextarea.addEventListener('input', updateCharCount);
                updateCharCount();
            }
            
            // Vérification en temps réel de la date
            async function checkDate() {
                const clientId = clientSelect.value;
                const date = dateInput.value;
                
                if (!date) {
                    warningsDiv.innerHTML = '';
                    return;
                }
                
                try {
                    const url = `/api/check-date?date=${date}&type=shooting${clientId ? '&client_id=' + clientId : ''}`;
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    warningsDiv.innerHTML = '';
                    
                    if (data.conflicts && data.conflicts.length > 0) {
                        // Filtrer le conflit actuel (celui qu'on modifie)
                        const otherConflicts = data.conflicts.filter(c => c.id != excludeId);
                        
                        otherConflicts.forEach(conflict => {
                            const conflictDiv = document.createElement('div');
                            conflictDiv.className = conflict.isSameClient ? 'alert alert-warning' : 'alert alert-info';
                            const icon = conflict.isSameClient ? '⚠️' : 'ℹ️';
                            const eventIcon = conflict.eventType === 'publication' ? '📢' : '📹';
                            conflictDiv.innerHTML = `
                                <span style="font-size: 1.2rem;">${icon}</span>
                                <span>${eventIcon} ${conflict.message}</span>
                                <a href="${conflict.url}" target="_blank" style="margin-left: auto; color: inherit; text-decoration: underline; font-weight: 600;">Voir</a>
                            `;
                            warningsDiv.appendChild(conflictDiv);
                        });
                    }
                    
                    if (data.warnings && data.warnings.length > 0) {
                        data.warnings.forEach(warning => {
                            const warningDiv = document.createElement('div');
                            warningDiv.className = 'alert alert-warning';
                            warningDiv.innerHTML = `
                                <span style="font-size: 1.2rem;">⚠️</span>
                                <span>${warning}</span>
                            `;
                            warningsDiv.appendChild(warningDiv);
                        });
                    } else if (!data.conflicts || data.conflicts.filter(c => c.id != excludeId).length === 0) {
                        const successDiv = document.createElement('div');
                        successDiv.className = 'alert alert-success';
                        successDiv.style.cssText = 'padding: 0.75rem 1rem; border-radius: 10px; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 4px solid #28a745; color: #155724;';
                        successDiv.innerHTML = `
                            <span style="font-size: 1.2rem;">✅</span>
                            <span>Aucun conflit détecté pour cette date</span>
                        `;
                        warningsDiv.appendChild(successDiv);
                    }
                } catch (error) {
                    console.error('Date check error:', error);
                }
            }
            
            dateInput.addEventListener('change', checkDate);
            clientSelect.addEventListener('change', () => {
                setTimeout(checkDate, 500);
            });
            
            // Form submission animation
            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="btn-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="2" x2="12" y2="6"></line>
                                <line x1="12" y1="18" x2="12" y2="22"></line>
                                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                <line x1="2" y1="12" x2="6" y2="12"></line>
                                <line x1="18" y1="12" x2="22" y2="12"></line>
                                <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                            </svg>
                        </span>
                        <span class="btn-text">Modification en cours...</span>
                    `;
                    
                    if (typeof gsap !== 'undefined') {
                        gsap.to(submitBtn, {
                            scale: 0.98,
                            duration: 0.2,
                            yoyo: true,
                            repeat: 1
                        });
                    }
                });
            }
            
            // Ensure all elements are visible first
            const allGsapElements = document.querySelectorAll('.publication-create-container [data-gsap]');
            allGsapElements.forEach((el) => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            });
            
            // GSAP animations if available
            if (typeof gsap !== 'undefined') {
                const fadeInElements = document.querySelectorAll('.publication-create-container [data-gsap="fadeIn"]');
                fadeInElements.forEach((el, index) => {
                    gsap.from(el, {
                        opacity: 0,
                        duration: 0.6,
                        delay: index * 0.1,
                        ease: 'power2.out'
                    });
                });
                
                const fadeInUpElements = document.querySelectorAll('.publication-create-container [data-gsap="fadeInUp"]');
                fadeInUpElements.forEach((el, index) => {
                    gsap.from(el, {
                        opacity: 0,
                        y: 30,
                        duration: 0.6,
                        delay: index * 0.1,
                        ease: 'power2.out'
                    });
                });
            }
        });
    </script>
@endsection
