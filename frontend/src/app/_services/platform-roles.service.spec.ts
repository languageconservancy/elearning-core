import { TestBed } from '@angular/core/testing';

import { PlatformRolesService } from './platform-roles.service';

describe('PlatformRolesService', () => {
  let service: PlatformRolesService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PlatformRolesService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
