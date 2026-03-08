@extends('layouts.app')

@section('title', 'Pacientes - Nutriólogo')

@section('page-title', 'Gestión de Pacientes')

@section('navigation')
    <a href="{{ route('nutriologo.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('nutriologo.pacientes.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-500 rounded-lg text-white">
        <i class="fas fa-child"></i>
        <span>Pacientes</span>
    </a>
    <a href="{{ route('nutriologo.evaluaciones.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-clipboard-check"></i>
        <span>Evaluaciones</span>
    </a>
    <a href="{{ route('nutriologo.menus.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-utensils"></i>
        <span>Menús</span>
    </a>
    <a href="{{ route('nutriologo.reportes.index') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-500 rounded-lg transition">
        <i class="fas fa-chart-bar"></i>
        <span>Reportes</span>
    </a>
@endsection

@section('content')
    <!-- Barra de búsqueda y filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex-1 md:mr-4">
                <div class="relative">
                    <input type="text" placeholder="Buscar paciente por nombre..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            <div class="flex space-x-3">
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option>Todas las edades</option>
                    <option>0-2 años</option>
                    <option>3-5 años</option>
                    <option>6-10 años</option>
                    <option>11-15 años</option>
                </select>
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option>Todos los estados</option>
                    <option>Normal</option>
                    <option>Bajo peso</option>
                    <option>Sobrepeso</option>
                    <option>Obesidad</option>
                </select>
                <a href="{{ route('nutriologo.pacientes.create') }}" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Paciente</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de pacientes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IMC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Evaluación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-child text-green-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">María González</div>
                                    <div class="text-sm text-gray-500">maria.gonzalez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">8 años</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18.5</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Normal</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15 Nov 2024</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('nutriologo.pacientes.show', 1) }}" class="text-green-600 hover:text-green-900 mr-3">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('nutriologo.pacientes.edit', 1) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-child text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Juan Pérez</div>
                                    <div class="text-sm text-gray-500">juan.perez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5 años</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">16.2</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Bajo peso</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">10 Nov 2024</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('nutriologo.pacientes.show', 2) }}" class="text-green-600 hover:text-green-900 mr-3">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('nutriologo.pacientes.edit', 2) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-child text-red-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Ana Martínez</div>
                                    <div class="text-sm text-gray-500">ana.martinez@email.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">12 años</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">22.8</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sobrepeso</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8 Nov 2024</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('nutriologo.pacientes.show', 3) }}" class="text-green-600 hover:text-green-900 mr-3">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('nutriologo.pacientes.edit', 3) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="text-sm text-gray-700">
                Mostrando <span class="font-medium">1</span> a <span class="font-medium">3</span> de <span class="font-medium">24</span> pacientes
            </div>
            <div class="flex space-x-2">
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Anterior</button>
                <button class="px-3 py-1 bg-green-600 text-white rounded-md text-sm">1</button>
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">2</button>
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Siguiente</button>
            </div>
        </div>
    </div>
@endsection

