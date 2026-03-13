import axios from 'axios';

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || 'http://ukkpaket2frans.test/api';
// const API_BASE_URL =
//   process.env.NEXT_PUBLIC_API_URL || 'https://api.parkingapp.com/api';

export const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export interface ParkingArea {
  id: number;
  nama_area: string;
  alamat: string;
  latitude: number | null;
  longitude: number | null;
  kapasitas: number;
  terisi: number;
  sisa: number;
  occupancy_rate: number;
  status: string;
  tarifs?: {
    mobil?: TarifRates;
    motor?: TarifRates;
  };
}

export interface Vehicle {
  id: number;
  plat_nomor: string;
  jenis_kendaraan: string;
  user_id: number;
  status?: string;
}

export interface TarifRates {
  tarif_per_menit: number;
  tarif_per_jam: number;
  tarif_akumulasi_menit?: number;
  tarif_akumulasi_jam?: number;
  denda_inap_per_hari: number;
}

export interface ParkingSession {
  id: number;
  ticket_code: string;
  vehicle_id: number;
  parking_area_id: number;
  entry_time: string;
  exit_time: string | null;
  duration: number | null;
  status: 'active' | 'completed' | 'cancelled';
  vehicle: Vehicle;
  parking_area: ParkingArea;
}

export interface User {
  id: number;
  name: string;
  email: string;
  roles: string[];
}

export interface LoginResponse {
  user: User;
  token: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: 'owner';
}

export interface BookingReservationRequest {
  vehicle_type: 'Mobil' | 'Motor';
  vehicle_plate: string;
  parking_area_id: number;
  estimated_duration?: number;
  notes?: string;
}

export interface BookingResponse {
  id: number;
  ticket_code: string;
  vehicle_type: string;
  vehicle_plate: string;
  parking_area: {
    id: number;
    nama_area: string;
    alamat: string;
  };
  booking_time: string;
  check_in_time?: string;
  estimated_duration?: number;
  status: string;
  can_check_in: boolean;
  expires_at: string;
}

export interface CheckInRequest {
  ticket_code: string;
}

export interface BookingRequest {
  vehicle_type?: string;
  vehicle_id?: number;
  parking_area_id: number;
  estimated_duration?: number;
}

export interface ExitRequest {
  ticket_code: string;
}

// Auth API
export const authAPI = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    const response = await api.post('/login', { email, password });
    return response.data;
  },
  register: async (data: RegisterRequest): Promise<LoginResponse> => {
    const response = await api.post('/register', data);
    return response.data;
  },
};

// Parking Areas API
export const parkingAreasAPI = {
  getAll: async (): Promise<ParkingArea[]> => {
    const response = await api.get('/area-parkir');
    console.log(response.data);
    return response.data.data;
  },
  getById: async (id: number): Promise<ParkingArea> => {
    const response = await api.get(`/area-parkir/${id}`);
    return response.data;
  },
};

// Booking API
export const bookingAPI = {
  book: async (data: BookingRequest): Promise<any> => {
    const response = await api.post('/booking', data);
    return response.data;
  },
  exit: async (data: ExitRequest): Promise<any> => {
    const response = await api.post('/booking/exit', data);
    return response.data;
  },
  getUserSessions: async (page = 1): Promise<any> => {
    const response = await api.get(`/booking/sessions?page=${page}`);
    return response.data;
  },
  getActiveSession: async (): Promise<ParkingSession | null> => {
    try {
      const response = await api.get('/booking/active');
      return response.data;
    } catch (error: any) {
      if (error.response?.status === 404) return null;
      throw error;
    }
  },
};

// Booking Reservation API
export const bookingReservationAPI = {
  book: async (data: BookingReservationRequest): Promise<BookingResponse> => {
    const response = await api.post('/booking-reservation', data);
    return response.data;
  },
  checkIn: async (data: CheckInRequest): Promise<any> => {
    const response = await api.post('/booking-reservation/check-in', data);
    return response.data;
  },
  myBookings: async (): Promise<{ bookings: BookingResponse[] }> => {
    const response = await api.get('/booking-reservation/my-bookings');
    return response.data;
  },
  cancel: async (id: number): Promise<any> => {
    const response = await api.delete(`/booking-reservation/${id}`);
    return response.data;
  },
};

// Parking Sessions API
export const parkingSessionsAPI = {
  getAll: async (params?: {
    status?: string;
    parking_area_id?: number;
    page?: number;
  }): Promise<any> => {
    const searchParams = new URLSearchParams();
    if (params?.status) searchParams.append('status', params.status);
    if (params?.parking_area_id) searchParams.append('parking_area_id', params.parking_area_id.toString());
    if (params?.page) searchParams.append('page', params.page.toString());

    const response = await api.get(`/parking-sessions?${searchParams}`);
    return response.data;
  },
  getByTicketCode: async (ticketCode: string): Promise<ParkingSession> => {
    const response = await api.get(`/parking-sessions/${ticketCode}`);
    return response.data;
  },
  getActiveSessions: async (params?: { parking_area_id?: number }): Promise<any> => {
    const searchParams = new URLSearchParams();
    if (params?.parking_area_id) searchParams.append('parking_area_id', params.parking_area_id.toString());

    const response = await api.get(`/parking-sessions/active?${searchParams}`);
    return response.data;
  },
  cancel: async (ticketCode: string): Promise<any> => {
    const response = await api.post(`/parking-sessions/${ticketCode}/cancel`);
    return response.data;
  },
};

export interface RatesResponse {
  mobil: TarifRates | null;
  motor: TarifRates | null;
}

export const vehicleAPI = {
  getAll: async (): Promise<any> => {
    const response = await api.get('/vehicles');
    console.log('Vehicle API Response:', response.data);
    return response.data;
  },
  getRates: async (areaId: number): Promise<RatesResponse> => {
    const response = await api.get(`/booking/rates?area_id=${areaId}`);
    return response.data.data;
  },
};
