import { habitsApi } from '../habitsApi';
import { HabitsRepository } from '../habitsRepository';

jest.mock('../habitsApi', () => ({
  habitsApi: {
    getRegistros: jest.fn(),
  },
}));

describe('HabitsRepository.loadRegistros', () => {
  const repository = new HabitsRepository();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('obtiene registros desde la API', async () => {
    const registros = [
      {
        id: 1,
        ninoHabitoId: 10,
        fecha: '2026-07-30',
        completado: true,
        registradoEn: '2026-07-30T12:00:00.000Z',
      },
    ];

    (habitsApi.getRegistros as jest.Mock).mockResolvedValue({
      data: registros,
      total: 1,
      page: 1,
      per_page: 100,
    });

    const result = await repository.loadRegistros(7);

    expect(habitsApi.getRegistros).toHaveBeenCalledWith(7);
    expect(result).toEqual(registros);
  });
});
